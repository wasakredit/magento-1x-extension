<?php

class Wasa_Wkcheckout_Helper_Data extends Mage_Core_Helper_Abstract
{
 
  protected $_wkcheckout;

  public function __construct()
  {
    $this->_wkcheckout = Mage::getModel('wkcheckout/wkcheckout');
  }  

  function getPaymentGatewayUrl() 
  {
    return Mage::getUrl('wkcheckout/checkout/gateway', array('_secure' => false));
  }


  public function validateProductLeasingAmount($product)
  {    
    if(!$product) return null;
    $price = $product->getPrice();
    $validation = $this->_wkcheckout->validateLeasingAmount($price);
    return $validation;
  }

  public function validateAllowedCurrency()
  {    
    $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();    
    return ($currency_code == 'SEK') ? true : false;
  }

  public function calculateLeasingCost($product)
  {
    
    $leasingValidation = $this->validateProductLeasingAmount($product);
    if (!$leasingValidation) return null;    

    $productsArray     = array();
    $productsArray[]   = $product;    
    $response          = $this->_wkcheckout->calculateProductLeasingCost($productsArray);
    $leasingCostsArray = $response[0];
    $monthlyCost       = $leasingCostsArray['monthly_cost'];
    $leasingAmount     = $monthlyCost['amount'];

    return $leasingAmount;

  }

  public function calculateLeasingCosts($products)
  {
    if(!$products) return null;
    if(!$this->validateAllowedCurrency()) return null;
    $response = $this->_wkcheckout->calculateProductCollectionLeasingCosts($products);    
    return $response;
  }  

  public function createProductWidget($product)
  {
    if(!Mage::getStoreConfig('payment/wkcheckout/active')) return null;
    if(!$this->validateAllowedCurrency()) return null;
    $response = $this->_wkcheckout->createProductWidget($product);
    return $response;
  }

}
