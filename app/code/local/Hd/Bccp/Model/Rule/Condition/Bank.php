<?php
class Hd_Bccp_Model_Rule_Condition_Bank
    extends Hd_Bccp_Model_Rule_Condition_Abstract
{
    protected $_entityKey = 'hd_bccp_bank_name';
    
    public function loadAttributeOptions() 
    {
        $attributes = array(
            'bank' => Mage::helper('hd_bccp')->__('Bank')
        );
        $this->setAttributeOption($attributes);
        return $this;
    }
    
}
