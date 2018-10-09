<?php
class Ecloud_Shipmentfilter_Model_Carriers
{
	public function toOptionArray()
    {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        foreach ($methods as $_code) {
            $carrier = $_code->getId();
            $_methodOptions[] = array('value' => $carrier, 'label' => Mage::getStoreConfig("carriers/$carrier/title"));
            
        }
       return $_methodOptions;
    }
   
}
		