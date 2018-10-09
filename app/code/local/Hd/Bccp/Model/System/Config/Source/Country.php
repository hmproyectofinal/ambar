<?php
class Hd_Bccp_Model_System_Config_Source_Country
{
    
    const XML_CONFIG_PATH_ALLOW_SPECIFIC    = 'hd_bccp/country_support/allowspecific';
    
    const XML_CONFIG_PATH_SPECIFIC_COUNTRY  = 'hd_bccp/country_support/specificcountry';
    
    
    protected $_countries;


    public function toOptionArray($includeEmpty = true, $reload = false, $emptyKey = '*')
    {
        if (!$this->_countries || $reload) {
            $countries = Mage::getSingleton('adminhtml/system_config_source_country')
                ->toOptionArray();

            if ($includeEmpty) {
                $countries[0]['label'] = Mage::helper('hd_bccp')->__('All Countries');
                $countries[0]['value'] = $emptyKey;
            } else {
                unset($countries[0]);
            }

            if (Mage::getStoreConfigFlag(self::XML_CONFIG_PATH_ALLOW_SPECIFIC)) {
                $allowedCountries = explode(',',Mage::getStoreConfig(self::XML_CONFIG_PATH_SPECIFIC_COUNTRY));
                foreach ($countries as $k => $v) {
                    if (!in_array($v['value'], $allowedCountries) && $k > 0) {
                        unset($countries[$k]);
                    }
                }
            }
            $this->_countries = $countries;
        }
        return $this->_countries;
    }
    
    public function toOptionHash($includeEmpty = true, $reload = false, $emptyKey = '*')
    {
        $options = array();
        foreach ($this->toOptionArray($includeEmpty, $reload, $emptyKey) as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }
}
