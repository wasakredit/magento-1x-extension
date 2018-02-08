<?php

/**
 * Custom order Statuses source model
 */
class Wasa_Wkcheckout_Model_System_Config_Source_Dropdown_Values
{
    // set null to enable all possible
    protected $_stateStatuses = array(
        Mage_Sales_Model_Order::STATE_NEW,
//        Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
        Mage_Sales_Model_Order::STATE_PROCESSING,
        Mage_Sales_Model_Order::STATE_COMPLETE,
        Mage_Sales_Model_Order::STATE_CLOSED,
        Mage_Sales_Model_Order::STATE_CANCELED,
        Mage_Sales_Model_Order::STATE_HOLDED,
    );

    public function toOptionArray()
    {
        if ($this->_stateStatuses) {
            $statuses = Mage::getSingleton('sales/order_config')->getStateStatuses($this->_stateStatuses);
        }
        else {
            $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
        }
        $options = array();
        $options[] = array(
          'value' => '',
          'label' => Mage::helper('adminhtml')->__('-- Please Select --')
        );
  $options[] = array(
    'value' => 'pending_wasa_checkout',
          'label' => Mage::helper('adminhtml')->__('Pending Wasa Checkout')
        );     
        foreach ($statuses as $code=>$label) {          
            $options[] = array(
               'value' => $code,
               'label' => $label
            );
        }
        return $options;
    }
}
