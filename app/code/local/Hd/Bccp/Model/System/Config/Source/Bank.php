<?php
class Hd_Bccp_Model_System_Config_Source_Bank
{
    
    protected $_options;

    public function toOptionArray($includeEmpty = false, $countryId = null, $storeIds = null)
    {
        if (!$this->_options) {
            
            $options     = array();            
            $collection = Mage::getResourceModel('hd_bccp/bank_collection');            
            if(!is_null($countryId)) {
                $collection->addCountryFilter($countryId);
            }
            if(!is_null($storeIds)) {
                $collection->addStoreFilter($storeIds);
            }
            if ($includeEmpty) {
                $options[0]['label'] = Mage::helper('hd_bccp')->__('-- Please Select --');
                $options[0]['value'] = '';
            }
            foreach ($collection as $item) {
                $options[] = array(
                    'label' => $item->getName(),
                    'value' => $item->getId(),
                );
            }
            $this->_options = $options;
        }
        return $this->_options;
    }
    
    public function toOptionHash($includeEmpty = false, $countryId = null, $storeIds = null)
    {
        $options = array();
        foreach ($this->toOptionArray($includeEmpty, $countryId, $storeIds = null) as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }
}
