<?php
class Todopago_Modulodepago2_Model_Cybersource_Factorycybersource {

	const RETAIL = "Retail";
	const SERVICE = "Service";
	const DIGITAL_GOODS = "Digital Goods";
	const TICKETING = "Ticketing";

	public static function get_cybersource_extractor($vertical, $order, $customer){
		$instance;
		switch ($vertical) {
			case Todopago_Modulodepago2_Model_Cybersource_Factorycybersource::RETAIL:
				$instance = new Todopago_Modulodepago2_Model_Cybersource_Retail($order, $customer);
			break;
			
			case Todopago_Modulodepago2_Model_Cybersource_Factorycybersource::SERVICE:
				$instance = new Todopago_Modulodepago2_Model_Cybersource_Service($order, $customer);
			break;
			
			case Todopago_Modulodepago2_Model_Cybersource_Factorycybersource::DIGITAL_GOODS:
				$instance = new Todopago_Modulodepago2_Model_Cybersource_Digitalsgoods($order, $customer);
			break;

			default:
				$instance = new Todopago_Modulodepago2_Model_Cybersource_Retail($order, $customer);
			break;
		}
		return $instance;
	}
}