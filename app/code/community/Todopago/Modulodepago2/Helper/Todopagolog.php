<?php
class Todopago_Modulodepago2_Helper_Todopagolog extends Mage_Core_Helper_Abstract
{
	
	public function log($message){
		$magento_version = Mage::getVersion();
		$php_version = phpversion();
		Mage::log("[Mag:$magento_version - php: $php_version] ".$message );
	}


}