<?php
class Databunch_Labels_Model_Config_Location
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 10, 'label'=>Mage::helper('labels')->__('Top-left')),
            array('value' => 20, 'label'=>Mage::helper('labels')->__('Top-right')),
            array('value' => 30, 'label'=>Mage::helper('labels')->__('Bottom-left')),
            array('value' => 40, 'label'=>Mage::helper('labels')->__('Bottom-right')),
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
            10 => Mage::helper('labels')->__('Top-left'),
            20 => Mage::helper('labels')->__('Top-right'),
            30 => Mage::helper('labels')->__('Bottom-left'),
            40 => Mage::helper('labels')->__('Bottom-right'),
        );
    }

}