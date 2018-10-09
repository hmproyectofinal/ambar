<?php
class Hd_Base_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_CONFIG_PATH_TRANLATE_REWRITE_ACTIVE = 'hd_base/rewrite/translate';
    
    public function isEnterprise() 
    {
        return Mage::getConfig()->getModuleConfig('Enterprise_Enterprise') 
            && Mage::getConfig()->getModuleConfig('Enterprise_AdminGws') 
            && Mage::getConfig()->getModuleConfig('Enterprise_Checkout') 
            && Mage::getConfig()->getModuleConfig('Enterprise_Customer');
    }
    
    public function isTranslateRewriteActive()
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_PATH_TRANLATE_REWRITE_ACTIVE);
    }
}
	 