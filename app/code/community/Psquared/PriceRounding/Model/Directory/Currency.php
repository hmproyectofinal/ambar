<?php
class Psquared_PriceRounding_Model_Directory_Currency extends Mage_Directory_Model_Currency
{
    /**
     * Convert price to currency format
     *
     * @param   double $price
     * @param   string $toCurrency
     * @return  double
     */
    public function convert($price, $toCurrency = null) {
        if (is_null($toCurrency)) {
            return $price;
        } elseif ($rate = $this->getRate($toCurrency)) {
            $priceValue = $price * $rate;
            if (Mage::getStoreConfig('catalog/price_rounding/enable_price_rounding') == 1){
                $priceValue = $this->_roundPrice($priceValue);
            }
            return $priceValue;
        }
        throw new Exception(Mage::helper('directory')->__('Undefined rate from "%s-%s".', $this->getCode(), $toCurrency->getCode()));
    }

    public function format($price, $options=array(), $includeContainer = true, $addBrackets = false)
    {
        return $this->formatPrecision($price, 0, $options, $includeContainer, $addBrackets);
    }

    protected function _roundPrice($price) {
        switch (Mage::getStoreConfig('catalog/price_rounding/rounding_method')){
            case 1:
                $roundedPrice = round($price);
                break;
            case 2:
                $roundedPrice = ceil($price);
                break;
            default:
                $roundedPrice = floor($price);
        }
        return $roundedPrice;
    }

    public function formatTxt($price, $options = array())
    {
        if (!is_numeric($price)) {
            $price = Mage::app()->getLocale()->getNumber($price);
        }
        /**
         * Fix problem with 12 000 000, 1 200 000
         *
         * %f - the argument is treated as a float, and presented as a floating-point number (locale aware).
         * %F - the argument is treated as a float, and presented as a floating-point number (non-locale aware).
         */
        $price = sprintf("%F", $price);
        $priceOutput = Mage::app()->getLocale()->currency($this->getCode())->toCurrency($price, $options);
        return $priceOutput;
    }
}
		