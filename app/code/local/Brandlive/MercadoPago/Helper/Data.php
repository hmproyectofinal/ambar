<?php

class Brandlive_MercadoPago_Helper_Data extends Mage_Core_Helper_Abstract{
	
	const COLLECTION_MAX_LIMIT 			= 50;
	const MIN_EXPIRATION_DURATION  		= 5;
	const MIN_ORDER_EXPIRATION_DURATION = 5;

	public function getCancelationCronEnabled($website = null){
		return (bool)Mage::app()->getWebsite($website)->getConfig('payment/mercadopago/cancelation_cron_enabled');
	}

	public function getExpiresEnabled($website = null){
		return (bool)Mage::app()->getWebsite($website)->getConfig('payment/mercadopago/expires');
	}

	public function getExpirationDuration($website = null){
		$expiration =  (int)Mage::app()->getWebsite($website)->getConfig('payment/mercadopago/expiration_duration');
		if ($expiration < self::MIN_EXPIRATION_DURATION) $expiration = self::MIN_EXPIRATION_DURATION;
		return $expiration;
	}

	public function getCancelationCronExpiration($website = null){
		$expiration =  (int)Mage::app()->getWebsite($website)->getConfig('payment/mercadopago/cancelation_cron_expiration');
		if ($expiration < self::MIN_ORDER_EXPIRATION_DURATION) $expiration = self::MIN_ORDER_EXPIRATION_DURATION;
		return $expiration;
	}

	public function getCancelationCronCollectionLimit($website = null){
		$limit = (int)Mage::app()->getWebsite($website)->getConfig('payment/mercadopago/cancelation_cron_collection_limit');
		if ($limit > self::COLLECTION_MAX_LIMIT) $limit = self::COLLECTION_MAX_LIMIT;
		return $limit;
	}

	public function getCancelationCronOrderStatuses($website = null){
		$statuses = Mage::app()->getWebsite($website)->getConfig('payment/mercadopago/cancelation_cron_order_statuses');
		return explode(',',$statuses);
	}

	public function getCancelationCronLogEnabled($website = null){
		return (bool)Mage::app()->getWebsite($website)->getConfig('payment/mercadopago/cancelation_cron_log');
	}
	
}
