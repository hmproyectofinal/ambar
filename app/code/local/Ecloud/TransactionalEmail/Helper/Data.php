<?php
class Ecloud_TransactionalEmail_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getCarriers() {
		$methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
		
		$options = array();

		foreach($methods as $_code => $_method) {
			if(!$_title = Mage::getStoreConfig("carriers/$_code/title")) {
				$_title = $_code;
			}
			$options[] = array('value' => $_code, 'label' => $_title . " ($_code)");
		}

		return $options;
	}

	public function getEstados() {
		$estados = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
		
		$options = array();

		foreach($estados as $est) {
			
			$options[] = array('value' => $est["status"], 'label' => $est["label"]." (".$est["status"].")");
		}

		return $options;
	}

	public function getTemplates() {
		$templates = Mage::getModel('core/email_template')->getCollection()->toOptionArray();

		return $templates;
	}
}