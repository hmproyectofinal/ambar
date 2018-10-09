<?php

class Psquared_PriceRounding_Model_RoundingMechanisms
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=> Mage::helper('pricerounding')->__('In Currency Conversion')),
            array('value' => 2, 'label'=> Mage::helper('pricerounding')->__('After Catalog Loading')),
        );
    }
}