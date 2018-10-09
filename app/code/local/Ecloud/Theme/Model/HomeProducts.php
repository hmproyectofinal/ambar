<?php
class Ecloud_Theme_Model_HomeProducts extends Mage_Core_Model_Abstract {

	protected function _construct()
    {
        $this->_init('ecloudtheme/homeproducts');
    }

  	public function getRecentProducts() {
  		Mage::log('Ecloud/Theme/homeproducts');
  		return;
		  $products = Mage::getModel("catalog/product")
            	-­>getCollection()
            	->addAttributeToSelect('*')
				      ­->setOrder('entity_id', 'DESC')
				      ->setPageSize(4);
    	 return $products;
  	}
}