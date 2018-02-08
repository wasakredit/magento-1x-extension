<?php

class Wasa_Wkcheckout_Model_Checkout extends Mage_Payment_Model_Method_Abstract {
  
  protected $_code  = 'wkcheckout';
  protected $_formBlockType = 'wkcheckout/form_wkcheckout';
  protected $_infoBlockType = 'wkcheckout/info_wkcheckout';
  protected $_isInitializeNeeded = true;

  protected $_wkcheckout;  

  public function __construct()
  {
    $this->_wkcheckout = Mage::getModel('wkcheckout/wkcheckout', array());
  }  

  public function isAvailable()
  {
    $isEnabled     = Mage::getStoreConfig('payment/wkcheckout/active');  
    $currencyValidation = Mage::helper('wkcheckout')->validateAllowedCurrency();
    // TODO: Add shipping cost
    $shippingCost  = 0;    
    $subTotal      = $this->_wkcheckout->getCart()->getQuote()->getSubtotal();
    $finalCost     = $shippingCost + $subTotal;
    $isWithinRange = $this->_wkcheckout->validateLeasingAmount($finalCost);    
    $validation    = $currencyValidation && $isEnabled && $isWithinRange ? true : false;
    return $validation;
  }

  public function initialize($paymentAction, $stateObject)
  {
    $sessionCheckout = Mage::getSingleton('checkout/session');
    $checkoutURL = $this->_wkcheckout->getCheckoutURL();
    $sessionCheckout->setData('checkoutURL', $checkoutURL);
    return $this;    
  }

  public function getOrderPlaceRedirectUrl()
  {
    return Mage::getUrl('wkcheckout/checkout/redirect', array('_secure' => false));
  }


}
