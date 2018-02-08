<?php
class Wasa_Wkcheckout_Model_System_Config_Source_Button_Values
{
  /**
   * Provide available options as a value/label array
   *
   * @return array
   */
  public function toOptionArray()
  {
    return array(
      array('value'=>1, 'label'=>' On'),
      array('value'=>0, 'label'=>' Off')                        
    );
  }
}