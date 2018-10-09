<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */
class Amasty_Sorting_Block_Featured extends Mage_Catalog_Block_Product_Abstract
{
    protected $listErrorMessages = array();

    public function getCollection()
    {
        $layer      = Mage::getSingleton('catalog/layer');
        $categoryId = $this->getData('category');
        
        // Store Current Category
        $currentCategory = $layer->getCurrentCategory();
        
        if ($categoryId) {
        	$category = Mage::getModel('catalog/category')->load($categoryId);
        	if ($category) { 
        		$layer->setCurrentCategory($category);
        	}
        } else {
			$rootCategoryId = (int) Mage::app()->getStore()->getRootCategoryId();
			if($rootCategoryId === $currentCategory->getId() && !$currentCategory->getIsAnchor()) {
				$msg = "Please open admin > catalog > categories, select the root category (ID:{$rootCategoryId}) and set Is Anchor option to YES.";
                $this->addErrorMessage($msg);
				return array();
			}
		}
        
        $collection = $layer->getCurrentCategory()->getProductCollection();        
        $layer->prepareProductCollection($collection);        
        
        $collection->addStoreFilter();
        
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection); 
        
        if ($this->getSorting()){
            $method = Mage::getModel('amsorting/method_' . $this->getSorting()); 
            if (!$method){
                $msg = "Please provide one of the following sorting methods:";
                foreach (Mage::helper('amsorting')->getMethods() as $className){
                    $msg .= "$className; ";
                }
                $this->addErrorMessage($msg);
                return array();
            }

            Mage::getModel('amsorting/method_image')->apply($collection,''); //required sorting by image, if setting enabled
            // it's special method ut it uses default attribute.
            if ('new' == $this->getSorting() && !Mage::getStoreConfig('amsorting/general/new_attr')){ 
                $collection->addAttributeToSort('created_at','desc');
            }
            else {
                $old = $method->isEnabled();
                $method->setEnabled(true);
                $method->apply($collection, 'desc');
                $method->setEnabled($old);
            }
        }
        elseif($this->getDefSorting()){
            $collection->addAttributeToSort($this->getDefSorting(), $this->getDefDirection() == 'desc' ? 'desc' : 'asc'); 
        }
        else {
            $msg = 'Please use param `sorting` or `def_sorting`';
            $this->addErrorMessage($msg);
            return array();            
        }
        
        $collection->setPage(1, $this->getLimit());
        
        // Restore Current Category
        $layer->setCurrentCategory($currentCategory);
        
        return $collection;
    }

    public function getListErrorMessages()
    {
        return $this->listErrorMessages;
    }

    protected function addErrorMessage($msg)
    {
        $this->listErrorMessages[] = $msg;
    }
    
}
