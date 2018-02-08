<?php

class Wasa_Wkcheckout_Model_Meta extends Mage_Core_Model_Abstract {
  
  protected $_partner_id;
  protected $_client_secret;    
  protected $_test_mode;  

  public function __construct()
  {    
    $this->_partner_id = self::getPartnerID();
    $this->_client_secret = self::getClientSecret();
    $this->_test_mode = self::getTestMode();    
  }

  /**
   * Retrieves and decrypts partner id
   * 
   * @return string $partnerID
   */     
  private function getPartnerID()
  {
    $encryptedValue = Mage::getStoreConfig('payment/wkcheckout/partner_id');
    $partnerID = Mage::helper('core')->decrypt($encryptedValue);
    return $partnerID;
  }  

  /**
   * Retrieves and decrypts client secret
   * 
   * @return string $clientSecret
   */         
  private function getClientSecret()
  {
    $encryptedValue = Mage::getStoreConfig('payment/wkcheckout/client_secret_id');  
    $clientSecret = Mage::helper('core')->decrypt($encryptedValue);
    return $clientSecret;
  }

  /**
   * Returns a bool to check if test mode is on/off
   * 
   * @return string $clientSecret
   */         
  private function getTestMode()
  {
    $testMode = Mage::getStoreConfig('payment/wkcheckout/test_mode');
    return $testMode;
  }    
  

}

