<?php
class Hd_Nps_Block_System_Config_Button_Reset 
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $this->getUrl('adminhtml/bccp_mock/reset');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel('Reset BCCP Data')
            ->setOnClick("setLocation('$url')")
            ->setId($element->getHtmlId())
            ->toHtml();

        return $html;
    }
    
}
