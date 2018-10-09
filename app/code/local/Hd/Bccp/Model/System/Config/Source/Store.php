<?php
class Hd_Bccp_Model_System_Config_Source_Store 
    extends Mage_Adminhtml_Model_System_Config_Source_Store
{
    public function toOptionHash($includeDefault = true)
    {
        $hash = array();
        if($includeDefault) {
            $hash[0] = Mage::helper('hd_bccp')->__('All Stores');
        }
        foreach(parent::toOptionArray() as $option)  {
            $hash[$option['value']] = $option['label'];
        }
        return $hash;
    }
}

