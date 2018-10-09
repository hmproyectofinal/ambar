<?php

class Psquared_PriceRounding_Block_Product_Price extends Mage_Catalog_Block_Product_Price
//class Psquared_PriceRounding_Block_Product_Price extends FireGento_GermanSetup_Block_Catalog_Product_Price
{
	public function getDisplayMinimalPrice(){
		if (Mage::getStoreConfig('catalog/price_rounding/rounding_machanism') == 2){
			return false;
		}
		return parent::getDisplayMinimalPrice();
    }
}