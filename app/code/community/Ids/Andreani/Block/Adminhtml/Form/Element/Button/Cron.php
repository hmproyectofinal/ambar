<?php
/**
 * Created by Jhonattan Campo
 * Date: 19/07/16
 * @name: Ids_Andreani_Block_Adminhtml_Form_Element_Button_Cron
 * @description: Bloque para la gestion del boton de "Generar Json Sucursales" para el System->Config->Sales->Payment Methods->Andreani
 * @category: Ids
 * @package: Ids_Andreani
 */

class Ids_Andreani_Block_Adminhtml_Form_Element_Button_Cron extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @description: Se setean las propiedades del elemento tipo "button" para disparar el cron de sucursales.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element){
        $this->setElement($element);

        $urlCronSucursales = Mage::helper('adminhtml')->getUrl('adminhtml/cron/index');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                                  ->setType('button')
                                  ->setLabel(Mage::helper('andreani')->__('Generar Json Sucursales'))
                                  ->setOnClick("openPopup('" . $urlCronSucursales . "')")
                                  ->toHtml();

        return $html;
    }
}