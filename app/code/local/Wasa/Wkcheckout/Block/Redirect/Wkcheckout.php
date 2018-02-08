<?php

class Wasa_Wkcheckout_Block_Redirect_Wkcheckout extends Mage_Core_Block_Template
{
  
  protected function _construct()
  {
    parent::_construct();
  }

  protected function _prepareLayout() {
    $sessionCheckout = Mage::getSingleton('checkout/session');
    $checkoutURL = $sessionCheckout->getData('checkoutURL');
    $this->setCheckoutURL($checkoutURL);
  }


}
