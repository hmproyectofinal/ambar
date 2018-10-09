<?php

class Psquared_PriceRounding_Model_Observer
{
	/**
	 * Modify prices on catagory page
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function catchCatalogProductCollectionLoadAfter($observer){
		if (Mage::getStoreConfig('catalog/price_rounding/enable_price_rounding') == 1
		&& Mage::getStoreConfig('catalog/price_rounding/rounding_machanism') == 2){
			foreach ($observer->getEvent()->getCollection() as $product){
				$this->_adjustProductPrice($product);
			}
		}
	}

	/**
	 * Modify prices on product detail page
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function catchCatalogProductLoadAfter($observer){
		if (Mage::getStoreConfig('catalog/price_rounding/enable_price_rounding') == 1
		&& Mage::getStoreConfig('catalog/price_rounding/rounding_machanism') == 2){
			$product = $observer->getEvent()->getProduct();
			$this->_adjustProductPrice($product);
		}
	}

	protected function _adjustProductPrice($product){
		$sku = $product->getSku();
		$baseCurrencyCode = Mage::app()->getBaseCurrencyCode();
		$currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
		$currencyRates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));

		$currentCurrencyRate = $currencyRates[$currencyCode];

		$price = $product->getPrice();
		$specialPrice = $product->getSpecialPrice();
		$finalPrice = $product->getFinalPrice();
			
		$finalPrice = $this->_adjustPrice($currentCurrencyRate, $finalPrice);
		$product->setFinalPrice($finalPrice);
			
		//recalculate the special price, if this is set
		if (!empty($specialPrice)){
			$specialPrice = $this->_adjustPrice($currentCurrencyRate, $specialPrice);
			$product->setSpecialPrice($specialPrice);
		}
			
		$price = $this->_adjustPrice($currentCurrencyRate, $price);
		$product->setPrice($price);
		
		return $product;
	}

	/**
	 * Multiply the price with the currency and round it
	 *
	 * @param float $currencyRate
	 * @param float $price
	 * @return float
	 */
	protected function _adjustPrice($currencyRate, $price){
		//if ($currencyRate != 1){
			$currencyPrice = $price * $currencyRate;

			switch (Mage::getStoreConfig('catalog/price_rounding/rounding_method')){
				case 1:
					$roundedCurrencyPrice = round($currencyPrice);
					break;
				case 2:
					$roundedCurrencyPrice = ceil($currencyPrice);
					break;
				default:
					$roundedCurrencyPrice = floor($currencyPrice);
			}
			$priceDelta = $roundedCurrencyPrice - $currencyPrice;
			$basePriceDelta = $priceDelta / $currencyRate;
			$price = $price + $basePriceDelta;
		//}

		return $price;
	}
}