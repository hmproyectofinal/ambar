<?php
class Treggo_Customshippingmethod_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getExtensionVersion() 
    {
        return (string) Mage::getConfig()->getNode()->modules->Treggo_Customshippingmethod->version;
    }
}
