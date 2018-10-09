<?php require_once Mage::getBaseDir('lib') . '/Andreani/wsseAuth.php';
class Ids_Andreani_Helper_Data extends Mage_Core_Helper_Abstract
{
	const ENVMODPROD 						= 'prod';
	const ENVMODTEST 						= 'testing';
	const COTIZACION 						= 'cotizacion';
	const TRAZABILIDAD 						= 'trazabilidad';
	const IMPRESIONCONSTANCIA 				= 'impresionconstancia';
	const OBTESTADODIST 					= 'obtenerestadodistribucion';
	const SUCURSALES 						= 'sucursales';
	const CONFIRCOMPRA 						= 'confirmacioncompra';
	const GENENVIOENTREGARETIROIMPRESION 	= 'generarenviosdeentregayretirocondatosdeimpresion';
	const ANULARENVIOS					 	= 'anularenvios';



	public function getTrackingpopup($tracking) {

		$collection = Mage::getModel('andreani/order')->getCollection()
			->addFieldToFilter('cod_tracking', $tracking);
		$collection->getSelect()->limit(1);

		if (!$collection) {
			Mage::log("Andreani :: no existe la orden en la tabla andreani_order.");
			return false;
		}

		foreach($collection as $thing) {
			$datos = $thing->getData();
		}

		if (Mage::getStoreConfig('carriers/andreaniconfig/testmode',Mage::app()->getStore()) == 1) {
			$url  = $this->getUrlWS(Ids_Andreani_Helper_Data::ENVMODTEST);
		} else {
			$url  = $this->getUrlWS(Ids_Andreani_Helper_Data::ENVMODPROD);
		}

		$datos["username"]	= Mage::getStoreConfig('carriers/andreaniconfig/usuario',Mage::app()->getStore());
		$datos["password"]  = Mage::getStoreConfig('carriers/andreaniconfig/password',Mage::app()->getStore());

		if ($datos["username"] == "" OR $datos["password"] == "") {
			Mage::log("Andreani :: no existe nombre de usuario o contraseña para eAndreani");
			return;
		}

		try {
			$options = array(
				'soap_version'	=> SOAP_1_2,
				'exceptions'	=> 1,
				'trace'			=> 1,
				'style'			=> SOAP_DOCUMENT,
				'encoding'		=> SOAP_LITERAL
			);

			$optRequest["ObtenerTrazabilidad"] = array(
				'Pieza' => array(
					'NroPieza'		=> '',
					'NroAndreani'	=> $tracking,
					'CodigoCliente'	=> $datos['cliente']
				));

			$client 	= new SoapClient($url, $options);
			$request 	= $client->__soapCall("ObtenerTrazabilidad", $optRequest);

			foreach( $request->Pieza->Envios->Envio->Eventos as $indice => $valor )
			{
				$eventos[$indice]["Fecha"] 		= $valor->Fecha;
				$eventos[$indice]["Estado"] 	= $valor->Estado;
				$eventos[$indice]["Motivo"] 	= $valor->Motivo;
				$eventos[$indice]["Sucursal"] 	= $valor->Sucursal;
			}

			$estadoenvio = array(
				"Nropieza" 					=> 		$request->Pieza->NroPieza,
				"NombreEnvio" 				=> 		$request->Pieza->Envios->Envio->NombreEnvio,
				"Codigotracking" 			=> 		$request->Pieza->Envios->Envio->NroAndreani,
				"FechAlta"					=>		$request->Pieza->Envios->Envio->FechaAlta,
				"Eventos" 					=> 		$eventos
			);

			return $estadoenvio;

		} 	catch (SoapFault $e) {
			Mage::log(print_r($e,true));
		}

	}

	public function getWeight() {
		$peso 	= 11;
		$medida = 1000;

		$cart = Mage::getModel('checkout/cart')->getQuote();
		foreach ($cart->getAllItems() as $item) {
			$datos["cantidad"][] 	= $item->getProduct()->getQty();
			$datos["peso"][] 		= $item->getProduct()->getWeight();
			$datos["name"][]		= $item->getProduct()->getName();

			$datos["total"]		 = ($item->getProduct()->getQty() * $item->getProduct()->getWeight() * $medida) + $datos["total"];

		}

		return $datos;
	}

	/**
	 * @description Método que espera un ambiente (testing o prod), para devolver la url correspondiente.
	 * @param $enviroment
	 * @return mixed
	 */
	public function getUrlWS($enviroment)
	{
		if($enviroment == 'prod')
		{
			$url = Mage::getStoreConfig('carriers/andreaniconfig/wsprod',Mage::app()->getStore());
		}
		else
		{
			$url = Mage::getStoreConfig('carriers/andreaniconfig/wstesting',Mage::app()->getStore());
		}
		return $url;
	}

	/**
	 * @description Método que espera un parámetro con el método que definirá la url a traer para el
	 * WS; además, es posible pasarle el ambiente (testing o prod) para que traiga la url correspondiente.
	 * @param $method
	 * @param null $enviroment
	 * @return mixed
	 */
	public function getWSMethodUrl($method,$enviroment=null)
	{
		if($enviroment == 'prod')
		{
			$configField = 'carriers/andreaniconfigwsprod/';
		}
		else
		{
			$configField = 'carriers/andreaniconfigwstest/';
		}

		switch($method)
		{
			case 'cotizacion':
				$url = Mage::getStoreConfig($configField.$method,Mage::app()->getStore());
				break;
			case 'trazabilidad':
				$url = Mage::getStoreConfig($configField.$method,Mage::app()->getStore());
				break;
			case 'impresionconstancia':
				$url = Mage::getStoreConfig($configField.$method,Mage::app()->getStore());
				break;
			case 'obtenerestadodistribucion':
				$url = Mage::getStoreConfig($configField.$method,Mage::app()->getStore());
				break;
			case 'sucursales':
				$url = Mage::getStoreConfig($configField.$method,Mage::app()->getStore());
				break;
			case 'confirmacioncompra':
				$url = Mage::getStoreConfig($configField.$method,Mage::app()->getStore());
				break;
			case 'generarenviosdeentregayretirocondatosdeimpresion':
				$url = Mage::getStoreConfig($configField.$method,Mage::app()->getStore());
				break;
			default:
				$url = '';
				break;
		}

		return $url;

	}

	/**
	 * @description método que devuelve los datos de usuario y password que están configurados
	 * en el admin de Andreani.
	 * @return array
	 */
	public function getUserCredentials()
	{
		$userCredentials = array();

		$userCredentials["username"]      = Mage::getStoreConfig('carriers/andreaniconfig/usuario',Mage::app()->getStore());
		$userCredentials["password"]      = Mage::getStoreConfig('carriers/andreaniconfig/password',Mage::app()->getStore());

		return $userCredentials;
	}

	/**
	 * @description obtiene el JSON de sucursales de la base de datos.
	 * @return mixed
	 */
	public function getJsonSucursales()
	{
		$sucursalesCollection = Mage::getModel('andreani/sucursales')->getCollection()
			->addFieldToSelect('json');
		foreach ($sucursalesCollection->getData() AS $sucursalesData) {
			$sucursales = unserialize($sucursalesData['json']);
		}
		return $sucursales;
	}

	public function getProvinciaLocalidadBySucursal($cod_sucursal){
		$sucursalesData = json_decode(Mage::helper('andreani')->getJsonSucursales());
		foreach ($sucursalesData AS $provincia => $localidades) {
			foreach ($localidades as $localidad => $sucursales) {
				foreach ($sucursales as $sucursal){
					if($sucursal->Sucursal == $cod_sucursal){
						$response['provincia'] = $provincia;
						$response['localidad'] = $localidad;
						return $response;
					}
				}
			}
		}
	}
	/**
	 * @description devuelve si la caché del módulo para las sucursales está activa.
	 * @return mixed
	 */
	public function getIsCacheWS()
	{
		return Mage::getStoreConfig('carriers/andreaniconfig/ws_cache',Mage::app()->getStore());
	}

	/**
	 * @description devuelve el objeto "address" consultándolo en el "quote".
	 * @return mixed
	 */
	public function getAddressFromQuote()
	{
		$quote	 = Mage::getModel('checkout/cart')->getQuote();
		$address = $quote->getShippingAddress();
		return $address;
	}


	/**
	 * @description recibe el número de andreani y crea el código de barras a partir del texto.
	 * @param $texto
	 */
	public function crearCodigoDeBarras($texto) {
		$texto = strtoupper($texto);
		$code_string = "";
		$chksum = 103;
		$code_array = array(" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "NUL" => "111422", "SOH" => "121124", "STX" => "121421", "ETX" => "141122", "EOT" => "141221", "ENQ" => "112214", "ACK" => "112412", "BEL" => "122114", "BS" => "122411", "HT" => "142112", "LF" => "142211", "VT" => "241211", "FF" => "221114", "CR" => "413111", "SO" => "241112", "SI" => "134111", "DLE" => "111242", "DC1" => "121142", "DC2" => "121241", "DC3" => "114212", "DC4" => "124112", "NAK" => "124211", "SYN" => "411212", "ETB" => "421112", "CAN" => "421211", "EM" => "212141", "SUB" => "214121", "ESC" => "412121", "FS" => "111143", "GS" => "111341", "RS" => "131141", "US" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "CODE B" => "114131", "FNC 4" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112");
		$code_keys = array_keys($code_array);
		$code_values = array_flip($code_keys);
		for ($x = 1; $x <= strlen($texto); $x++) {
			$activeKey = substr($texto, ($x - 1), 1);
			$code_string .= $code_array[$activeKey];
			$chksum = ($chksum + ($code_values[$activeKey] * $x));
		}
		$code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];
		$code_string = "211412" . $code_string . "2331112";
		$code_length = 20;
		for ($i = 1; $i <= strlen($code_string); $i++) {
			$code_length = $code_length + (integer) (substr($code_string, ($i - 1), 1));
		}
		$img_width = $code_length;
		$img_height = 50;
		$image = imagecreate($img_width, $img_height);
		$black = imagecolorallocate($image, 0, 0, 0);
		$white = imagecolorallocate($image, 255, 255, 255);
		imagefill($image, 0, 0, $white);
		$location = 10;
		for ($position = 1; $position <= strlen($code_string); $position++) {
			$cur_size = $location + ( substr($code_string, ($position - 1), 1) );
			imagefilledrectangle($image, $location, 0, $cur_size, $img_height, ($position % 2 == 0 ? $white : $black));
			$location = $cur_size;
		}

		$filePath 		= Mage::getBaseDir('media')."/uploads/default/";

		if (!file_exists($filePath) || !is_dir($filePath)) {
			mkdir("{$filePath}", 0777,true);
		}
		$filename 		= $filePath."{$texto}.png";

		imagepng($image, $filename);
		imagesavealpha($image, true);
		imagedestroy($image);
	}

	public function getSoapVersion($method,$enviroment=null)
	{
		if($enviroment == 'prod')
		{
			$configField = 'carriers/andreaniconfigwsprod/';
		}
		else
		{
			$configField = 'carriers/andreaniconfigwstest/';
		}

		switch($method)
		{
			case 'cotizacion': $soapVersion = Mage::getStoreConfig($configField.'cotizacion_soap_version',Mage::app()->getStore());
				break;
			case 'trazabilidad': $soapVersion = Mage::getStoreConfig($configField.'trazabilidad_soap_version',Mage::app()->getStore());
				break;
			case 'impresionconstancia': $soapVersion = Mage::getStoreConfig($configField.'impresionconstancia_soap_version',Mage::app()->getStore());
				break;
			case 'obtenerestadodistribucion': $soapVersion = Mage::getStoreConfig($configField.'obtenerestadodistribucion_soap_version',Mage::app()->getStore());
				break;
			case 'sucursales': $soapVersion = Mage::getStoreConfig($configField.'sucursales_soap_version',Mage::app()->getStore());
				break;
			case 'confirmacioncompra': $soapVersion = Mage::getStoreConfig($configField.'confirmacioncompra_soap_version',Mage::app()->getStore());
				break;
			case 'generarenviosdeentregayretirocondatosdeimpresion': $soapVersion = Mage::getStoreConfig($configField.'generarenviosdeentregayretirocondatosdeimpresion_soap_version',Mage::app()->getStore());
				break;
			case 'anularenvios': $soapVersion = Mage::getStoreConfig($configField.'anularenvio_soap_version',Mage::app()->getStore());
				break;
			default: $soapVersion = '';
				break;
		}

		return $soapVersion;
	}

}