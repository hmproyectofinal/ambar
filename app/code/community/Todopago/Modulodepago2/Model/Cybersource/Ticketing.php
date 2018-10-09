<?php
class Todopago_Modulodepago2_Model_Cybersource_Ticketing extends Todopago_Modulodepago2_Model_Cybersource_Cybersource{

	protected function completeCSVertical(){
		return $this->getMultipleProductsInfo();
	}

	protected function getCategoryArray($product_id){
		return Mage::helper('modulodepago2/data')->getCategoryTodopago($product_id);
	}
}