<?php

class Psquared_PriceRounding_Model_RoundingMethods
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=> Mage::helper('pricerounding')->__('round')),
            array('value' => 2, 'label'=> Mage::helper('pricerounding')->__('ceil')),
            array('value' => 3, 'label'=> Mage::helper('pricerounding')->__('floor')),
        );
    }
}