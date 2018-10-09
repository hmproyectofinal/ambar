<?php
class Hd_Bccp_Model_Rule_Condition_Creditcard 
    extends Hd_Bccp_Model_Rule_Condition_Abstract
{
    protected $_entityKey = 'hd_bccp_cc_name';
    
    public function loadAttributeOptions() 
    {
        $attributes = array(
            'creditcard' => Mage::helper('hd_bccp')->__('Credit Card')
        );
        $this->setAttributeOption($attributes);
        return $this;
    }
    
}
