<?php
class Todopago_Modulodepago2_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getDevelopersEndpoint(){
		return 'https://developers.todopago.com.ar/services/t/1.1/';
	}

	public function getApisEndpoint(){
		return 'https://apis.todopago.com.ar/services/t/1.1/';
	}

    public function getFormatoNumero($numero) {
		$numero_to_arrays = explode ( ".", $numero );
		$numero_formateado = $numero_to_arrays [0] . "." . substr ( $numero_to_arrays [1], 0, 2 );
		if (strlen ( $numero_to_arrays [1] ) == 0) {
			$numero_formateado = $numero_formateado . "00";
		}
		if (strlen ( $numero_to_arrays [1] == 1 )) {
			$numero_formateado = $numero_formateado . "0";
		}
		return $numero_formateado;
	}
	public function diasTranscurridos($fecha_i, $fecha_f) {
		$dias = (strtotime ( $fecha_i ) - strtotime ( $fecha_f )) / 86400;
		$dias = abs ( $dias );
		$dias = floor ( $dias );
		return $dias;
	}
	public function getCategoryTodopago($code_id) {
		$cs_category;
		switch ($code_id) {
			case "0":
				$cs_category="default";
				break;
			case "1":
				$cs_category="adult_content";
				break;
			case "2":
				$cs_category="coupon";
				break;
			case "3":
				$cs_category="default";
				break;
			case "4":
				$cs_category="electronic_good";
				break;
			case "5":
				$cs_category="electronic_software";
				break;
			case "6":
				$cs_category="gift_certificate";
				break;
			case "7":
				$cs_category="handling_only";
				break;
			case "8":
				$cs_category="service";
				break;
			case "9":
				$cs_category="shipping_and_handling";
				break;
			case "10":
				$cs_category="shipping_only";
				break;
			case "11":
				$cs_category="subscription";
				break;
			
			default:
				$cs_category="default";
				break;
		}
		
		return $cs_category;
	}

	public function getTipoEnvioTodopago($code_id) {
		$cs_category;
		switch ($code_id) {
			case "0":
				$cs_category="no select";
				break;
			case "1":
				$cs_category="Pick up";
				break;
			case "2":
				$cs_category="Email";
				break;
			case "3":
				$cs_category="Smartphone";
				break;
			case "4":
				$cs_category="Other";
				break;
			
			default:
				$cs_category="Other";
				break;
		}
		return $cs_category;
	}

		public function getTipoServicioTodopago($code_id) {
		$cs_category;
		switch ($code_id) {
			case "0":
				$cs_category="no select";
				break;
			case "1":
				$cs_category="Luz";
				break;
			case "2":
				$cs_category="Gas";
				break;
			case "3":
				$cs_category="Telefono";
				break;
			case "4":
				$cs_category="Agua";
				break;

				case "5":
				$cs_category="TV";
				break;

				case "6":
				$cs_category="Cable";
				break;

				case "7":
				$cs_category="Internet";
				break;

				case "8":
				$cs_category="Impuestos";
				break;
			
			default:
				$cs_category="default";
				break;
		}
		return $cs_category;
	}

		public function getTipoDeliveryTodopago($code_id) {
		$cs_category;
		switch ($code_id) {
			case "0":
				$cs_category="no select";
				break;
			case "1":
				$cs_category="Pick up";
				break;
			case "2":
				$cs_category="Email";
				break;
			case "3":
				$cs_category="Smartphone";
				break;

			default:
			$cs_category="default";
		}
		return $cs_category;
	}
}
	 