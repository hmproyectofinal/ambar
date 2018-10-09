<?php
class Hd_Nps_Model_System_Config_Source_Url 
{
    public function toOptionArray()
    {
        $helper = Mage::helper('hd_nps');
        $options = array(
            array(
                'label' => $helper->__('Please Select'),
                'value' => '',
            )
        );
        $urls = Mage::getStoreConfig('hd_nps/web_service/urls');
        foreach($urls as $code => $url) {
            $options[] = array(
                'label' => $helper->__('Use %s Url', $helper->__(ucfirst($code))),
                'value' => $url,
            );
        }
        return $options;
    }
}
