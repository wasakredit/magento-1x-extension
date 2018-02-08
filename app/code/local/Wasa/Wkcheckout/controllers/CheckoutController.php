<?php
class Wasa_Wkcheckout_CheckoutController extends Mage_Core_Controller_Front_Action

{

  
  public function gatewayAction() 
  {
    
    if ($this->getRequest()->get("orderId"))
    {
      $arr_querystring = array(
        'flag' => 1, 
        'orderId' => $this->getRequest()->get("orderId")
      );
       
      Mage_Core_Controller_Varien_Action::_redirect('wkcheckout/checkout/response', array('_secure' => false, '_query'=> $arr_querystring));
    }

  }

  public function redirectAction() 
  {

    $this->loadLayout();
    $block = $this->getLayout()->createBlock('wkcheckout/redirect_wkcheckout')->setTemplate('wkcheckout/redirect.phtml');
    $this->getLayout()->getBlock('content')->append($block);
    $this->renderLayout();

  }
 
  public function responseAction() 
  {

    echo 'You have reached the response action';

    if ($this->getRequest()->get("flag") == "1" && $this->getRequest()->get("orderId")) 
    {
      $orderId = $this->getRequest()->get("orderId");
      $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
      $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, 'Payment Success.');
      $order->save();
       
      Mage::getSingleton('checkout/session')->unsQuoteId();
      Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=> false));
    }
    else
    {
      Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/error', array('_secure'=> false));
    }

  }

  public function completeAction()
  {    
    $wkcheckout = Mage::getModel('wkcheckout/wkcheckout');     
    $post_body = file_get_contents('php://input');
    $request = $wkcheckout->getCheckout(json_decode($post_body, true));
    $checkoutID = $request['checkout_id'];
    var_dump($checkoutID);
  }


  // ************************
  // Callback actions
  // ************************

  public function callbackCompletedAction()
  {
    $orderId = $this->getRequest()->getParam('order_id');
    $wasaOrderId = $this->getRequest()->getParam('wasa_order_id');
    $wkcheckout = Mage::getModel('wkcheckout/wkcheckout');
    $wkcheckout->addOrderReferences($wasaOrderId, $orderId);  
  }

  public function callbackCancelledAction()
  {    
    $redirectUrl = Mage::getStoreConfig('payment/advanced_options/confirmation_callback_url');

    $orderId = $this->getRequest()->getParam('order_id');
    $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
    $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, "Order canceled either by Wasa Kredit or user.");
    $order->setData('state', Mage_Sales_Model_Order::STATE_CANCELED);
    $order->save();

    echo $redirectUrl;
  }

  public function callbackRedirectedAction()
  {    
    $redirectUrl = Mage::getStoreConfig('payment/advanced_options/confirmation_callback_url');
    echo $redirectUrl;
  }  


  // ************************
  // Ping actions
  // ************************  

  public function pingAction()
  {      

    // Extract Wasa Kredit Order ID from POST body
    $data = json_decode(file_get_contents('php://input'), true);
    $orderId = $data["order_id"];    
    
    // Make request to retrieve Wasa Kredit Order from API
    $wkcheckout = Mage::getModel('wkcheckout/wkcheckout');
    $order = $wkcheckout->getOrder($orderId);

    // Retrieve the status of the Wasa Kredit Order
    $wasaOrderStatus = $order['status']['status']; 
    
    // Initialize variable for storing Magento Order Id, set to null by default
    $magentoOrderId = null;

    // Return if the Wasa Kredit Order does not contain any order references
    if(!$order['order_references']) return null;

    // Iterate over each item in order references
    foreach($order['order_references'] as $reference) {    
      // Return the item containing the Magento Order ID if it exists
      $targetOrder = array_filter($reference, function($ar) {
        return ($ar['magento_order_id']);
      });        
    }

    // Exit if the Magento Order ID does not exists
    if(!$targetOrder) return null;

    // Store the value of the Magento Order ID
    $magentoOrderId = $targetOrder['value'];
       
    // Load corrsponding Magento order with the Magento Order ID
    $order = Mage::getModel('sales/order')->loadByIncrementId($magentoOrderId);   
    
    // Return null if the Magento order is empty
    if(empty($order->getData())) return null;
    
    // Set status of Magento order to reflect status of Wasa Kredit Order
    // based on the status of the Wasa Kredit Order status
    switch($wasaOrderStatus) {
      case 'completed':
        $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE, "Order has been paid by Wasa Kredit.");
        $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
        break;
      case 'canceled':
        $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_CANCELED, "Order canceled either by Wasa Kredit or user.");
        $order->setData('state', Mage_Sales_Model_Order::STATE_CANCELED);
        break;
      case 'shipped':
        $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE, "Order marked as shipped by us when shipped.");
        $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
        break;
      case 'pending':
        $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE, "Awaiting information or more signatories. An order could stay in this state for multiple days.");
        $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
        break;
      case 'ready_to_ship':
        $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE, "Wasa Kredit has approved to finance this order.");
        $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
        break; 
      default:
        break; 
    }

    $order->save();    
       
  }


}
