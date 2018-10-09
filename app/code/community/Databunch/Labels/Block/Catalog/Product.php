<?php
class Databunch_Labels_Block_Catalog_Product extends Mage_Core_Block_Template
{
    protected $_product;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('databunch/labels/catalog/product.phtml');
    }

    public function setProduct($product)
    {
        $this->_product = $product;

        return $this;
    }

    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = Mage::registry('product');
        }

        return $this->_product;
    }

    public function isSale()
    {
        $enabled = Mage::getStoreConfig('labels/sale_label/enabled');
        if (!$enabled) {
            return false;
        }

        $product = $this->getProduct();

        if ($product->getPrice() > $product->getFinalPrice()) {
            return true;
        }

        return false;
    }

    public function getSaleText()
    {
        $text = Mage::getStoreConfig('labels/sale_label/text');

        if ($this->isSale()) {
            $showPercentage = Mage::getStoreConfig('labels/sale_label/show_percentage');
            if ($showPercentage) {
                $product = $this->getProduct();
                $percentageValue = round((abs($product->getPrice() - $product->getFinalPrice()) / $product->getPrice()) * 100);
                $limitPercentage = Mage::getStoreConfig('labels/sale_label/limit');
                if ($percentageValue >= $limitPercentage) {
                    $format = Mage::getStoreConfig('labels/sale_label/format');
                    if ($format) {
                        $text = sprintf($format, $percentageValue);
                    } else {
                        $text = "-" . $percentageValue . "%";
                    }
                }
            }
        }

        return $text;
    }

    public function getSaleBackgroundColor()
    {
        $backgroundColor = "#" . Mage::getStoreConfig('labels/sale_label/background_color');

        return $backgroundColor;
    }

    public function getSaleTextColor()
    {
        $textColor = "#" . Mage::getStoreConfig('labels/sale_label/text_color');

        return $textColor;
    }

    public function isNew()
    {
        $enabled = Mage::getStoreConfig('labels/new_label/enabled');
        if (!$enabled) {
            return false;
        }

        $product = $this->getProduct();

        $newFromDate = $product->getNewsFromDate();
        $newToDate = $product->getNewsToDate();
        $now = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        if(($newFromDate < $now && $newFromDate != NULL) && ($newToDate > $now || $newToDate == "")) {
            return true;
        }

        return false;
    }

    public function getNewText()
    {
        $text = Mage::getStoreConfig('labels/new_label/text');

        return $text;
    }

    public function getNewBackgroundColor()
    {
        $backgroundColor = "#" . Mage::getStoreConfig('labels/new_label/background_color');

        return $backgroundColor;
    }

    public function getNewTextColor()
    {
        $textColor = "#" . Mage::getStoreConfig('labels/new_label/text_color');

        return $textColor;
    }

    public function isFeatured()
    {
        $enabled = Mage::getStoreConfig('labels/featured_label/enabled');
        if (!$enabled) {
            return false;
        }

        $product = $this->getProduct();

        if ($product->getFeaturedProduct()) {
            return true;
        }

        return false;
    }

    public function getFeaturedText()
    {
        $text = Mage::getStoreConfig('labels/featured_label/text');

        return $text;
    }

    public function getFeaturedBackgroundColor()
    {
        $backgroundColor = "#" . Mage::getStoreConfig('labels/featured_label/background_color');

        return $backgroundColor;
    }

    public function getFeaturedTextColor()
    {
        $textColor = "#" . Mage::getStoreConfig('labels/featured_label/text_color');

        return $textColor;
    }

    public function getLocationClass()
    {
        $location = Mage::getStoreConfig('labels/general/location');
        switch ($location) {
            case 10: 
                $locationClass = "pl-top-left";
                break;
            case 20: 
                $locationClass = "pl-top-right";
                break;
            case 30: 
                $locationClass = "pl-bottom-left";
                break;
            case 40: 
                $locationClass = "pl-bottom-right";
                break;
            default: 
                $locationClass = "";
                break;
        }

        return $locationClass;
    }
} 