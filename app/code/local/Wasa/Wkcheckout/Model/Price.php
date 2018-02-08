<?php


// List view -> formatLeasingCost()
  
class Wasa_Wkcheckout_Model_Price extends Mage_Payment_Model_Method_Abstract {
  
  protected $_price;

  public function __construct($price)
  {
    
    switch(gettype($price)) {
      case "string":
        $this->_price = self::formatPriceString($price);        
        break;
      case "integer":
        $this->_price = self::formatPriceInt($price);        
        break;
      case "double":
        $this->_price = self::formatPriceDouble($price);        
        break;
      default:
        echo "Price not supported";
        break;
    }
    
  }

  public function getPrice() {
    return $this->_price;
  }

  public function getPriceAsString() {
    return (string)$this->_price;
  }  

  public function convertToString($price)
  {
    return (string)$this->_price;
  }

  public function exchangeCurrency($price)
  {
    return Mage::helper('core')->currency($price, false);
  }

  public function formatPriceString($price)  {    
    return round($this->exchangeCurrency($price), 2);  
  }

  public function formatPriceInt($price)
  {
  }
  
  public function formatPriceDouble($price)
  {    
    return round($this->exchangeCurrency($price), 2);
  }  



}
