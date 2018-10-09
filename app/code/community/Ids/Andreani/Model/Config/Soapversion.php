<?php
/**
 * Created by PhpStorm.
 * Date: 11/08/16
 * Class Ids_Andreani_Model_Config_Metodo
 */

class Ids_Andreani_Model_Config_Soapversion
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'SOAP_1_1', 'label'=>Mage::helper('adminhtml')->__('SOAP_1_1')),
            array('value' => 'SOAP_1_2', 'label'=>Mage::helper('adminhtml')->__('SOAP_1_2'))
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'SOAP_1_1' => Mage::helper('adminhtml')->__('SOAP_1_1'),
            'SOAP_1_2' => Mage::helper('adminhtml')->__('SOAP_1_2'),
        );
    }

}
