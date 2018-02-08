<?php

class Wasa_Wkcheckout_Model_ShotCaller extends Mage_Core_Model_Abstract {
  
  protected $_client_sdk;

  public function __construct($params)
  {
    require_once(Mage::getBaseDir('lib') . '/Wasa/client-php-sdk/Wasa.php');
    $this->_client_sdk = new Sdk\Client($params['partnerId'], $params['clientSecret'], $params['testMode']);
  }

  public function call($method, $payload)
  {


    if(!$payload) return null;

    $result = null;
          
    try
    {
      // Attempt API call
      $result = call_user_func(array($this->_client_sdk, $method), $payload);
      
      if(!$result->statusCode) return null;          
      if($this->validateStatusCode($result->statusCode)) return null;    
      if(!$result->data) return null;

      // Enable in developer mode      
      // if($result->errorMessage)
      // {
      //   throw new Exception('Oh no! The following error occurred: '.$result->errorMessage);
      // }

    }

    // CATCH if something goes wrong.
    catch (Exception $ex)
    {      
      echo $ex->getMessage();
      return null;
    }
    
    return $result->data;

  }

  public function callWithId($method, $id, $payload)
  {
    if(!$payload) return null;
    $result = null; 
    try
    {
      $result = call_user_func(array($this->_client_sdk, $method), $id, $payload);
      
      if(!$result->statusCode) return null;          
      if($this->validateStatusCode($result->statusCode)) return null;    
      if(!$result->data) return null;
    }

    catch (Exception $ex)
    {      
      echo $ex->getMessage();
      return null;
    }
    
    return $result->data;    

  }

  private function validateStatusCode($code)
  {
    if($code !== 200 || $code !== 201) return false;    
  }


}

