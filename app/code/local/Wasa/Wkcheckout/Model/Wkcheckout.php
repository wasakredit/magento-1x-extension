<?php

/**
 * Retrieves cart and customer information and interacts with API
 *
 * Interacts with API through Wasa Client PHP SDK.
 * Extract required information from cart and customer objects.
 *
 * @author Jim Skogman <jim.skogman@starrepublic.com> 
 */

require_once dirname(__FILE__).'/Meta.php';
require_once dirname(__FILE__).'/Price.php';

class Wasa_Wkcheckout_Model_Wkcheckout extends Wasa_Wkcheckout_Model_Meta
{

    protected $_shot_caller;    

    public function __construct()
    {
      
      parent::__construct();      
      $this->_shot_caller = Mage::getModel('wkcheckout/shotcaller', array('partnerId'    => $this->_partner_id,
                                                                          'clientSecret' => $this->_client_secret,
                                                                          'testMode'     => $this->_test_mode));      
    }
  
    
    /**
     * Retrieves Quote singleton
     * 
     * @return object $quote
     */       
    public function getQuote() 
    {
      $quote = Mage::getSingleton('checkout/session')->getQuote();
      return $quote;
    }


    /**
     * Retrieves the current cart
     * 
     * @return object $cart
     */       
    public function getCart() 
    {
      $cart = Mage::getModel('checkout/cart');
      return $cart;
    }    
    
    /**
     * Checks if user is logged in
     * 
     * @return bool $customerStatus
     */           
    public function getCustomerStatus() 
    {
      $customerStatus = Mage::getSingleton('customer/session')->isLoggedIn();
      return $customerStatus;
    }
  
    /**
     * Checks if billing/shipping addresses are equal
     * 
     * @return bool $addressType
     */    
    public function getAddressType()
    {
      $quote = $this->getQuote();
      $addressType = $quote->getShippingAddress()->getData('same_as_billing');    
      return $addressType;
    }
  
    /**
     * Retrieves payment mode
     * 
     * @return string $mode
     */        
    public function getMode()
    {
      $mode = 'PaymentProviderSmall';
      return $mode;
    }  
  
    /**
     * Retrieves payment type
     * 
     * @return string $type
     */    
    public function getPaymentType()
    {
      $type = 'leasing';
      return $type;
    }    
  
    /**
     * Retrieves the store currency
     * 
     * @return string $currency
     */  
    public function getStoreCurrency()
    {
      $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
      return $currency;    
    }
  
    /**
     * Retrieves the organisation number
     * 
     * @return int $orgNumber
     */      
    public function getOrgNumber() {
      $fieldId = Mage::getStoreConfig('payment/advanced_options/org_number_field_id');
      $orgNumberID = $fieldId ? $fieldId : 'vat_id';
      $quote = $this->getQuote();
      $orgNumber = $quote->getBillingAddress()->getData($orgNumberID);
      return $orgNumber;    
    }

    /**
     * Retrieves the request domain field from
     * the admin panel
     * @return int $requestDomain
     */      
    public function getRequestDomain() {
      $requestDomain = Mage::getStoreConfig('payment/advanced_options/request_domain');
      return $requestDomain;
    }

    /**
     * Retrieves the callback url from
     * the admin panel
     * @return int $callbackURL
     */          
    public function getConfirmationCallbackURL() {
      $callbackURL = Mage::getStoreConfig('payment/advanced_options/confirmation_callback_url');
      return $callbackURL;
    }

    /**
     * Retrieves the organisation number
     * 
     * @return int $orgNumber
     */    
    public function getTotalCost()
    {
      $quote = $this->getQuote();
      $grandTotal = $quote->getGrandTotal();  
      return $grandTotal;
    }    
  
  
    /**
     * Retrieves the total shipping cost
     * 
     * @return array $shippingCost
     */          
    public function getShippingCost()
    {
      $quote = $this->getQuote();
      $shippingAmount = $quote->getShippingAddress()->getShippingAmount();

      $currency = $this->getStoreCurrency();      
      $formattedCost = (string)$shippingAmount;
  
      $shippingCost = array(
        'amount'  => $formattedCost,
        'currency' => $currency
      );
  
      return $shippingCost;
  
    }
  
    /**
     * Retrieves customer details
     * 
     * @return array $personalDetails    
     */        
    public function getCustomerDetails()
    {  
      $customerAddress = $this->getBillingAddress();      
      $personalDetails = array(
        'purchaser_name'  => $customerAddress->getFirstname().' '.$customerAddress->getLastname(),
        'purchaser_phone' => $customerAddress->getTelephone(),
        'purchaser_email' => $customerAddress->getEmail()
      );
      
      return $personalDetails;
    }
  

    /**
     * Makes API call to retrieve checkout URL
     * 
     * @return string $response    
     */            
    public function getCheckoutURL() {      
  
      $requestDomain = $this->getRequestDomain();
      $confirmationCallbackURL = $this->getConfirmationCallbackURL();      

      $shippingCost = $this->getShippingCost();
      $mode = $this->getMode();    
      $paymentType = $this->getPaymentType();
      $orgNumber = $this->getOrgNumber();
      
      // Check if user is logged in
      $isLoggedIn = $this->getCustomerStatus();
      $customerDetails = NULL;
      $recipientDetails = NULL;
  
      $sessionCheckout = Mage::getSingleton('checkout/session');
      $orderId = $sessionCheckout->getQuoteId();     
  
      $customerDetails = $this->getCustomerDetails();
  
      // Check address type
      $addressIsSameAsBilling = $this->getAddressType();
      $billingAddress = NULL;
      $shippingAddress = NULL;
  
      if($addressIsSameAsBilling) {
        $billingAddress = $this->getAddress($addressIsSameAsBilling);
        $shippingAddress = $this->getAddress($addressIsSameAsBilling);
        $recipientDetails = $customerDetails;
      } else {        
        $billingAddress = $this->getAddress(!$addressIsSameAsBilling);
        $shippingAddress = $this->getAddress($addressIsSameAsBilling);
        $recipientDetails = $this->getRecipientDetails();
      }
      
      $cartItems = $this->getCartItems();
      
      $orderReferences = array();
      $orderReferences[] = array('key' => 'magento_quote_id', 'value' => $orderId);
      $pingUrl = Mage::getBaseUrl() . 'wkcheckout/checkout/ping';
  
      $payload = array (
        'partner_id' => $this->_partner_id,
        'payment_types' => $paymentType,
        'order_reference_id' => $orderId,
        'order_references' => $orderReferences,
        'cart_items' => $cartItems,
        'shipping_cost_ex_vat' => $shippingCost,
        'mode' => $mode,
        'customer_organization_number' => $orgNumber,
        'purchaser_name' => $customerDetails['purchaser_name'],
        'purchaser_email' => $customerDetails['purchaser_email'],
        'purchaser_phone' => $customerDetails['purchaser_phone'],
        'billing_address' => $billingAddress,
        'delivery_address' => $shippingAddress,
        'recipient_name' => $customerDetails['purchaser_name'],
        'recipient_phone' => $customerDetails['purchaser_phone'],
        'request_domain' => $requestDomain,
        'confirmation_callback_url' => $confirmationCallbackURL,
        'ping_url' => $pingUrl
      );

      $response = $this->_shot_caller->call('create_checkout', $payload);
      return $response;
  
    }

    /**
     * Make API call to retrieve order id
     * 
     * @return int $response
     */   
    public function getCheckout($id) {
      $response = $this->_shot_caller->call('get_checkout', $id);          
      return $response;
    }
  
    /**
     * Retrieves billing address core object
     * 
     * @return object $address    
     */  
    public function getBillingAddress() {
      $quote = $this->getQuote();
      $address = $quote->getBillingAddress();
      return $address;
    }
  
    /**
     * Retrieves shipping address core object
     * 
     * @return object $address    
     */  
    public function getShippingAddress() {
      $quote = $this->getQuote();
      $address = $quote->getShippingAddress();
      return $address;
    }  
  
    /**
     * Retrieves recipient details
     * 
     * @return array $personalDetails
     */      
    public function getRecipientDetails()
    {  
      $address = $this->getShippingAddress();
  
      $personalDetails = array(
        'purchaser_name'  => $address->getFirstname().' '.$address->getLastname(),
        'purchaser_phone' => $address->getTelephone(),
        'purchaser_email' => $address->getEmail()
      );
      
      return $personalDetails;      
  
    }
  
    /**
     * Get country id
     * 
     * @return string $countryName
     */  
    public function getCountryNameById($ID) {
      $countryModel = Mage::getModel('directory/country')->loadByCode($ID);
      $countryName = $countryModel->getName();
      return $countryName;        
    }  
  
    /**
     * Retrieves shipping or billing address
     * 
     * @return array $address
     */        
    public function getAddress($isSameAsBilling)
    {

      $address = $isSameAsBilling ? $this->getBillingAddress() : $this->getShippingAddress();
  
      $streetAddress = $address->getStreet();
      $streetElement = $streetAddress[0];
      
      $addressArray = array(
        'company_name'  => $address->getCompany(),
        'street_address' => $streetElement,
        'postal_code' => $address->getPostcode(),
        'city' => $address->getCity(),        
        'country' => $this->getCountryNameById($address->getCountryId())
      );
  
      return $addressArray;    
  
    } 
  
    /**
     * Calculates the tax rate percentage
     * 
     * @return int $percent
     */     
    public function calculateTaxRate($taxId)
    {
      $store = Mage::app()->getStore('default');      
      $taxCalculation = Mage::getModel('tax/calculation');      
      $request = $taxCalculation->getRateRequest(null, null, null, $store);    
      $percent = $taxCalculation->getRate($request->setProductClassId($taxId));
      return $percent;  
    }  
  
    /**
     * Casts parameter to string
     * 
     * @return string $value
     */       
    public function formatToString($value)
    {
      return (string)$value;
    }    
  
    public function getCartItems() 
    {
  
      $cartItems = array();                
      $cart = Mage::getModel('checkout/cart')->getQuote();
      $currency = $this->getStoreCurrency();
  
      foreach($cart->getAllItems() as $key=>$value) {

        $priceObj = new Wasa_Wkcheckout_Model_Price($value->getBasePrice());

        $baseTaxRate = $this->calculateTaxRate($value->getTaxClassId());
        $basePrice = $priceObj->getPrice();
        $baseTaxAmount = ($baseTaxRate/100) * $basePrice;

        $taxRate = $this->formatToString($baseTaxRate);
        $price = $priceObj->getPriceAsString();
        $taxAmount = $this->formatToString($baseTaxAmount);

        $cartItem = array(
          'product_id' => $value->getProductId(),
          'product_name' => $value->getName(),
          'price_ex_vat' => array(
            'amount' => $price,
            'currency' => $currency
          ),
          'quantity' => $value->getQty(),
          'vat_percentage' => $taxRate,
          'vat_amount' => array(
            'amount' => $taxAmount,
            'currency' => $currency
          ),
          'image_url' => ''       
          // 'image_url' => (string)Mage::helper('catalog/image')->init($value->getProduct(), 'thumbnail')
        );
        
        $cartItems[$key] = $cartItem;
  
      }
  
      return $cartItems;
  
    }
    
  
    /**
     * Makes API call and leasing options
     * 
     * @return array $json_response['contract_lengths']
     */      
    public function getLeasingOptions()
    {

      // TODO: Add shipping cost
      $shippingCost = 0;
      $subTotal = round($this->getCart()->getQuote()->getSubtotal());
                  
      $amount = $subTotal + $shippingCost;
      $currency = $this->getStoreCurrency();
        
      $totalAmount = array(
        'amount' => (string)$amount,
        'currency' => $currency
      );
  
      $calculateTotalLeasingCostBody = array (
        'partner_id' => $this->_partner_id,
        'total_amount' => $totalAmount,
      );
           
      $response = $this->_shot_caller->call('calculate_total_leasing_cost', $calculateTotalLeasingCostBody);            
      return $response['contract_lengths'];
  
    }


    /**
     * Checks if the price contains a period or comma and 
     * formats price to contain no more than two decimals 
     * 
     * @param string $price
     * 
     * @return string $formattedPrice
     */
    public function formatPrice($price)
    {
                     
     $adjustedPrice;      
     (!is_string($price)) ? $literalRepPrice = $price : $literalRepPrice = (string)$price;
     (strpos($literalRepPrice, ",") === false) ? $adjustedPrice = $literalRepPrice : $adjustedPrice = str_replace(",",".", $literalRepPrice);
      
     $dotIndex = strpos($adjustedPrice, ".") + 1;
     $length = strlen($adjustedPrice);
     $decimalSpan = $length - $dotIndex;
     $formattedPrice = (($decimalSpan) > 2) ? substr($adjustedPrice, 0, -($decimalSpan -2)) : $adjustedPrice;
      
     return $formattedPrice;

    }
    
    
    /**
     * Returns an array of products in the format
     * expected by _client_sdk->calculate_leasing_cost
     * 
     * @return array $filteredProducts
     */
    public function formatLeasingCosts($products)
    {
      
      $currency = $this->getStoreCurrency();
      $filteredProducts = array();      

      foreach($products as $product)
      { 
        $priceObj = new Wasa_Wkcheckout_Model_Price($product->getFinalPrice());

        $filteredProducts[] = array(
          'financed_price' => array(
            'amount' => $priceObj->getPrice() < 100000 ? $priceObj->getPriceAsString() : '1',
            'currency' => $currency
          ),
          'product_id' => $product->getEntityId()
        );
      }      

      return $filteredProducts;

    }


    /**
     * Calculate the leasing cost of a product
     * 
     * @return array $decodedResponse
     */   
    public function calculateProductLeasingCost($products)
    {
      
      $filteredProducts = $this->formatLeasingCosts($products);

      $payload = array (
        'partner_id' => $this->_partner_id,
        'items' => $filteredProducts
      );    
                  
      $response = $this->_shot_caller->call('calculate_leasing_cost', $payload);
      $leasingCosts = $response['leasing_costs'];
      return $leasingCosts;
  
    }


    /**
     * Calculate the leasing cost of a collection of products
     * 
     * @return array $filteredResponse
     */   
    public function calculateProductCollectionLeasingCosts($products)
    {
      
      $filteredProducts = $this->formatLeasingCosts($products);

      $payload = array (
        'partner_id' => $this->_partner_id,
        'items' => $filteredProducts
      );    

      $response = $this->_shot_caller->call('calculate_leasing_cost', $payload);               
      $leasingCosts = $response['leasing_costs'];

      $filteredResponse = array();
      foreach($leasingCosts as $cost)
      {
        $filteredResponse[$cost['product_id']] = $cost['monthly_cost']['amount'];
      }
      
      return $filteredResponse;
  
    }

    
    /**
     * Returns an HTML snippet for displaying
     * a product widget
     * 
     * @param int $productPrice
     * 
     * @return string $snippet
     */
    public function createProductWidget($product)
    {
    
      $priceObj = new Wasa_Wkcheckout_Model_Price($product->getFinalPrice());
      $price = $priceObj->getPriceAsString();
      $currency = $this->getStoreCurrency();

      $payload = array (
        'financial_product' => 'Leasing',
        'price_ex_vat' => array(
          'amount' => $price,
          'currency' => $currency
        )        
      );    

      $response = $this->_shot_caller->call('create_product_widget', $payload);
      return $response;
    }       

    
    /**
     * Check if a product is within the
     * allowed range to allow leasing
     * 
     * @param int $amount
     * 
     * @return bool $validation
     */
    public function validateLeasingAmount($amount)
    {
      $response = $this->_shot_caller->call('validate_allowed_leasing_amount', $amount);            
      $validation = $response['validation_result'];
      return $validation;
    }



    /**
     * Create mapping between Wasa order id
     * and Magento order id
     * 
     * @param int $order1
     * @param int $order2
     * 
     * @return bool $validation
     */
    public function addOrderReferences($order1, $order2)
    {

      $payload = array(
        'key' => 'magento_order_id',
        'value' => $order2
      );
      
      $this->_shot_caller->callWithId('add_order_reference', $order1, $payload);


    }


    /**
     * Get order object from order reference     
     * 
     * @param int $orderId     
     * 
     * @return bool $validation
     */
    public function getOrder($orderId)
    {      
      $response = $this->_shot_caller->call('get_order', $orderId);      
      return $response;
    }        


}
