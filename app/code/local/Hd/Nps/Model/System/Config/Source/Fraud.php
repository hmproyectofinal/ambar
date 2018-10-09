<?php
class Hd_Nps_Model_System_Config_Source_Fraud
{
    protected $attributeExclude = array(
        
        'customer' => array(
            
        ),
        'address' => array(
            
        ),
        'order' => array(
            
        ),
        'payment' => array(
            
        ),
    );

    protected function _toArray($options)
    {
        $result = array();
        foreach($options as $k => $v) {
            $result[] = array(
                'value' => $k,
                'label' => $v,
            );
        }
        return $result;
    }
    
    public function customerOptions()
    {
        $attributes = Mage::getModel('customer/customer')->getAttributes();
        $result = array();
        foreach ($attributes as $attribute) {
            if ($label = $attribute->getFrontendLabel()) {
                $result[$attribute->getAttributeCode()] = $label;
            }
        }
        return $result;
    }
    
    public function customerAddressOptions()
    {
//        $attributes = Mage::getModel('customer/address')->getAttributes();
        
        $result = array();
        return $result;
    }
    
    public function orderOptions()
    {
        $attributes = Mage::getModel('sales/order')->getAttributes();
        return $result;
    }
    
    public function autoOptions()
    {
        return array(
            '' => $this->_helper()->__('Skip Field'),
            'A' => $this->_helper()->__('Auto'),
        );
    }
    
    /**
     * @return Hd_Nps_Helper_Data
     */
    protected function _helper($key = 'data')
    {
        return Mage::helper("hd_nps/{$key}");
    }
    
    
            
}