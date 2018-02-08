<?php

class Wasa_Wkcheckout_Block_Form_Wkcheckout extends Mage_Payment_Block_Form
{

  protected $_wkcheckout;
  
  protected function _construct()
  {
    parent::_construct();
    $this->setTemplate('wkcheckout/form/wkcheckout.phtml');
  }

  protected function _prepareLayout() {  
          
    $this->_wkcheckout = Mage::getModel('wkcheckout/wkcheckout');    

    $leasingOptions = $this->_wkcheckout->getLeasingOptions();
    $this->setLeasingOptions($leasingOptions);                    

  }


}
