<?php
class Hd_Nps_Block_Checkout_Template extends Mage_Core_Block_Template
{
    protected function _getActionUrl($action = '')
    {
        return Mage::getUrl("*/*/{$action}", array('_secure' => $this->_isSecure()));
    }
    
    protected function _isSecure()
    {
        return Mage::app()->getStore()->isCurrentlySecure();
    }
    
    protected function _getIframeAllowedOrigin()
    {
        return trim(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK, $this->_isSecure()),'/');
    }
}

