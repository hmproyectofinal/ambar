<?php require_once Mage::getBaseDir('lib') . '/Andreani/wsseAuth.php';
class Ids_Andreani_Model_Observer extends Mage_Core_Model_Session_Abstract {

	/**
	 * Event: checkout_type_onepage_save_order
	 * @param $observer Varien_Event_Observer
	 */

	/**
	 * NOTA:
	 * - Llama a la funcion cuando la orden fue creada luego del Checkout y almacena los datos en la tabla "andreani_order"
	 */
	public function andreaniObserver($observer) {
		try {
			// 1. Tomamos todos los datos de la orden
			//fix. seteamos el contrato correcto
			$metodoenvio = $observer->getEvent()->getOrder()->getShippingMethod();
			if($metodoenvio == 'andreaniestandar_andreaniestandar'){
				$datos 		= Mage::getSingleton('core/session')->getAndreaniEstandar();
			}elseif($metodoenvio == 'andreaniurgente_andreaniurgente'){
				$datos 		= Mage::getSingleton('core/session')->getAndreaniUrgente();
			}elseif($metodoenvio == 'andreanisucursal_andreanisucursal'){
				$datos 		= Mage::getSingleton('core/session')->getAndreaniSucursal();
			}else{
				// No es envio con Andreani
				return;
			}


			// fix. setteamos datos de ship porque si la orden viene de admin, vienen vacios
			$ship = $observer->getEvent()->getOrder()->getShippingAddress();
			$datos["nombre"] = $ship->getFirstname();
			$datos["apellido"] = $ship->getLastname();
			$datos["telefono"] = $ship->getTelephone();

			// 2. Buscamos el ID de la orden y increment id
			$OrderId = $observer->getEvent()->getOrder()->getId();
			$OrderIncId = $observer->getEvent()->getOrder()->getIncrementId();

			// 3. Los almacenamos en la tabla "andreani_order"
			$_dataSave = (array(
				'id_orden' 		=> intval($OrderId),
				'order_increment_id' => intval($OrderIncId),
				'contrato' 		=> $datos["contrato"],
				'cliente'		=> $datos["cliente"],
				'direccion' 	=> $datos["direccion"],
				'localidad' 	=> $datos["localidad"],
				'provincia' 	=> $datos["provincia"],
				'cp_destino' 	=> $datos["cpDestino"],
				'sucursal_retiro' 		=> $datos["sucursalRetiro"],
				'direccion_sucursal'	=> $datos["DireccionSucursal"],
				'nombre' 		=> $datos["nombre"],
				'apellido' 		=> $datos["apellido"],
				'telefono' 		=> $datos["telefono"],
				'dni' 			=> $datos["dni"],
				'email' 		=> $datos["email"],
				'precio' 		=> $datos["precio"],
				'valor_declarado' 		=> $datos["valorDeclarado"],
				'volumen' 		=> $datos["volumen"],
				'peso' 			=> $datos["peso"],
				'detalle_productos' 	=> $datos["DetalleProductos"],
				'categoria_distancia_id'=> $datos["CategoriaDistanciaId"],
				'categoria_peso' 		=> $datos["CategoriaPeso"],
				'direccion_sucursal'	=> $datos["DireccionSucursal"],
				'estado'				=> 'Pendiente'
			));
			$model = Mage::getModel('andreani/order')->addData($_dataSave);
			$model->save();

		} catch (Exception $e) {
			Mage::log("Error: " . $e);
		}
	}

	/**
	 * NOTA: Llama a la funcion cuando desde el Admin Panel se ejecuta el "Ship" y luego "Submit Shipment"
	 */
	public function salesOrderShipmentSaveBefore($observer) {

		// 1. Tomamos los datos de la orden segun el ID en la tabla "andreani_order"
		$shipment = $observer->getEvent()->getShipment();
		$order 	  = $shipment->getOrder();

		/**
		 * ANDREANI-10
		 * En caso de que la orden no tenga como metodo de envio a andreani
		 * Los demas metodos de envio no deben verse afectados por los metodos de andreani
		 */
		$metodoenvio = $order->getShippingMethod();
		if($metodoenvio != 'andreaniestandar_andreaniestandar'
			&& $metodoenvio != 'andreaniurgente_andreaniurgente'
			&&$metodoenvio != 'andreanisucursal_andreanisucursal'){
			return;
		}

		$OrderId  = $order->getId();

		// Traemos los datos de la tabla "andreani_order" según el OrderId[0] y asignarla a $datos
		$collection = Mage::getModel('andreani/order')->getCollection()
			->addFieldToFilter('id_orden', $OrderId);
		$collection->getSelect()->limit(1);

		$datos = null;
		foreach($collection as $thing) {
			$datos = $thing->getData();
		}

		if (!$datos) {
			// No esta en la tabla andreani_order
			return;
		}

		if (Mage::getStoreConfig('carriers/andreaniconfig/testmode',Mage::app()->getStore()) == 1) {
			$urlDatosImpresion  = Mage::helper('andreani')->getWSMethodUrl(Ids_Andreani_Helper_Data::IMPRESIONCONSTANCIA,Ids_Andreani_Helper_Data::ENVMODTEST);
			$soapVersion  		= Mage::helper('andreani')->getSoapVersion(Ids_Andreani_Helper_Data::IMPRESIONCONSTANCIA,Ids_Andreani_Helper_Data::ENVMODTEST);
		} else {
			$urlDatosImpresion  = Mage::helper('andreani')->getWSMethodUrl(Ids_Andreani_Helper_Data::IMPRESIONCONSTANCIA,Ids_Andreani_Helper_Data::ENVMODPROD);
			$soapVersion	  	= Mage::helper('andreani')->getSoapVersion(Ids_Andreani_Helper_Data::IMPRESIONCONSTANCIA,Ids_Andreani_Helper_Data::ENVMODPROD);
		}


		$userCredentials = Mage::helper('andreani')->getUserCredentials();



		if ($userCredentials["username"] == "" OR $userCredentials["password"] == "") {
			Mage::log("Andreani :: no existe nombre de usuario o contraseña para eAndreani");
			return;
		}

		// Si el envio ya tiene un codigo de tracking no hacemos nada
		if ($datos["cod_tracking"] != ""){
			return;
		}
		Mage::log($datos,null,'andreani.log',true);
		// 2. Conectarse a eAndreani
		try {
			$options = array(
				'soap_version'		=> $soapVersion,
				'exceptions' 		=> true,
				'trace' 			=> 1,
				'wdsl_local_copy'	=> true
			);

			$wsse_header = new WsseAuthHeader($userCredentials['username'], $userCredentials['password']);
			$client         = new SoapClient($urlDatosImpresion, $options);
			$client->__setSoapHeaders(array($wsse_header));

			// Limitamos el detalle de productos a 90 caracteres para que lo tome el WS de Andreani
			if (strlen($datos["detalle_productos"]) >= 90){
				$datos["detalle_productos"] = substr($datos["detalle_productos"],0,80) . "...";
			}

			$phpresponse = $client->ConfirmarCompraConRecibo(array(
				'compra' =>array(
					'Calle'					=> $datos["direccion"],
					'CategoriaDistancia'	=> $datos["categoria_distancia_id"],
					'CategoriaFacturacion'	=> NULL,
					'CategoriaPeso' 		=> $datos["categoria_peso"],
					'CodigoPostalDestino' 	=> $datos["cp_destino"],
					'Contrato' 				=> $datos["contrato"],
					'Departamento' 			=> NULL,
					'DetalleProductosEntrega'=> $datos["detalle_productos"],
					'DetalleProductosRetiro' => $datos["detalle_productos"],
					'Email' 				=> $datos["email"],
					'Localidad' 			=> $datos["localidad"],
					'NombreApellido' 		=> $datos["nombre"] . " " . $datos["apellido"],
					'NombreApellidoAlternativo' => NULL,
					'Numero' 				=> ".",
					'NumeroCelular' 		=> $datos["telefono"],
					'NumeroDocumento' 		=> $datos["dni"],
					'NumeroTelefono' 		=> $datos["telefono"],
					'NumeroTransaccion' 	=> "Orden nro: " . $datos["order_increment_id"],
					'Peso' 					=> $datos["peso"],
					'Piso' 					=> NULL,
					'Provincia' 			=> $datos["provincia"],
					'SucursalCliente' 		=> NULL,
					'SucursalRetiro' 		=> $datos["sucursal_retiro"],
					'Tarifa' 				=> $datos["precio"],
					'TipoDocumento' 		=> "DNI",
					'ValorACobrar' 			=> "", // Si es contrarembolso deberiamos sumar el "ValorDeclarado" -- $datos["precio"]
					'ValorDeclarado' 		=> $datos["valor_declarado"],
					'Volumen' 				=> $datos["volumen"]
				)));


			// 4. Tomamos "NroAndreani" y lo almacenamos como "Tracking number"
			$shipment 	= $observer->getEvent()->getShipment();
			$track = Mage::getModel('sales/order_shipment_track')
				->setNumber($phpresponse->ConfirmarCompraResult->NumeroAndreani)
				->setCarrierCode('andreani') //carrier code
				->setTitle('Andreani');
			$shipment->addTrack($track);


			$id = intval($datos["id"]);
			Mage::getModel('andreani/order')->load($id)->setData('cod_tracking',$phpresponse->ConfirmarCompraResult->NumeroAndreani)->save();
			Mage::getModel('andreani/order')->load($id)->setData('recibo_tracking',$phpresponse->ConfirmarCompraResult->Recibo)->save();
			Mage::getModel('andreani/order')->load($id)->setData('estado','Enviado')->save();

		} catch (SoapFault $e) {
			Mage::log("Error: " . $e);
			Mage::throwException(Mage::helper('andreani')->__('Algo ha ido mal con la conexión a Andreani. Intente nuevamente. (envío no generado).'));

		}

	}

	/**
	 * NOTA: Despues de guardar el shippment, enviamos el mail al comprador con su tracking code
	 */
	public function salesOrderShipmentSaveAfter($observer) {
		$shipment 	= $observer->getEvent()->getShipment();
		$order 	  = $shipment->getOrder();

		/**
		 * ANDREANI-10
		 * En caso de que la orden no tenga como metodo de envio a andreani
		 * Los demas metodos de envio no deben verse afectados por los metodos de andreani
		 */
		$metodoenvio = $order->getShippingMethod();
		if($metodoenvio != 'andreaniestandar_andreaniestandar'
			&& $metodoenvio != 'andreaniurgente_andreaniurgente'
			&&$metodoenvio != 'andreanisucursal_andreanisucursal'){
			return;
		}

		// enviamos el mail con el tracking code
		if($shipment){
			if(!$shipment->getEmailSent()){
				$shipment->sendEmail(true,'');
				$shipment->setEmailSent(true);
				$shipment->save();
			}
		}
	}


	public function saveAndreaniDatosGuia($observer) {
		$object = $observer->getEvent()->getShipment();
		$order 	= $object->getOrder();

		/**
		 * ANDREANI-10
		 * En caso de que la orden no tenga como metodo de envio a andreani
		 * Los demas metodos de envio no deben verse afectados por los metodos de andreani
		 */
		$metodoenvio = $order->getShippingMethod();
		if($metodoenvio != 'andreaniestandar_andreaniestandar'
			&& $metodoenvio != 'andreaniurgente_andreaniurgente'
			&&$metodoenvio != 'andreanisucursal_andreanisucursal'){
			return;
		}
		Mage::log('entra al observer saveAndreaniDatosGuia ',null,'andreani.log',true);

		if (Mage::getStoreConfig('carriers/andreaniconfig/testmode',Mage::app()->getStore()) == 1) {
			$urlDatosImpresion  = Mage::helper('andreani')->getWSMethodUrl(Ids_Andreani_Helper_Data::GENENVIOENTREGARETIROIMPRESION,Ids_Andreani_Helper_Data::ENVMODTEST);
			$soapVersion  		= Mage::helper('andreani')->getSoapVersion(Ids_Andreani_Helper_Data::GENENVIOENTREGARETIROIMPRESION,Ids_Andreani_Helper_Data::ENVMODTEST);
		} else {
			$urlDatosImpresion  = Mage::helper('andreani')->getWSMethodUrl(Ids_Andreani_Helper_Data::GENENVIOENTREGARETIROIMPRESION,Ids_Andreani_Helper_Data::ENVMODPROD);
			$soapVersion	  	= Mage::helper('andreani')->getSoapVersion(Ids_Andreani_Helper_Data::GENENVIOENTREGARETIROIMPRESION,Ids_Andreani_Helper_Data::ENVMODPROD);
		}

		$options 	= array(
			'soap_version'		=> $soapVersion,
			'exceptions' 		=> true,
			'trace' 			=> 1,
			'wdsl_local_copy'	=> true
		);

		$userCredentials = Mage::helper('andreani')->getUserCredentials();



		$wsse_header    = new WsseAuthHeader($userCredentials['username'], $userCredentials['password']);
		$client         = new SoapClient($urlDatosImpresion, $options);
		$client->__setSoapHeaders(array($wsse_header));

		//Se conecta a la DB para obtener el resto de datos.
		$orderId 	= $observer->getShipment()->getOrder()->getEntityId();
		$collection = Mage::getModel('andreani/order')->getCollection()
			->addFieldToFilter('id_orden', $orderId);
		$collection->getSelect()->limit(1);

		$datosAndreani = '';
		foreach($collection AS $data) {
			$datosAndreani = $data->getData();
		}
		if ($datosAndreani['cod_tracking'])
			return false;

		$dataGuia = array();

		if (strlen($datosAndreani['detalle_productos']) >= 90){
			$datosAndreani['detalle_productos'] = substr($datosAndreani['detalle_productos'],0,80) . "...";
		}
		try {
			$phpresponse = $client->GenerarEnviosDeEntregaYRetiroConDatosDeImpresion(array(
				'parametros' => array(
					'Provincia' 					=> $datosAndreani['provincia'],
					'Localidad' 					=> $datosAndreani['localidad'],
					'CodigoPostal' 					=> $datosAndreani['cp_destino'],
					'Calle' 						=> $datosAndreani['direccion'],
					'Numero' 						=> '.',
					'Piso' 							=> null,
					'Departamento' 					=> null,
					'Nombre' 						=> $datosAndreani['nombre'],
					'Apellido' 						=> $datosAndreani['apellido'],
					'NombreAlternativo' 			=> null,
					'ApellidoAlternativo' 			=> null,
					'TipoDeDocumento' 				=>'DNI',
					'NumeroDeDocumento' 			=> $datosAndreani['dni'],
					'Email' 						=> $datosAndreani['email'],
					'TelefonoFijo' 					=> $datosAndreani['telefono'],
					'TelefonoCelular' 				=> $object->getShippingAddress()->getFax(),
					'CategoriaPeso' 				=> $datosAndreani['categoria_peso'],
					'Peso' 							=> $datosAndreani['peso'],
					'DetalleDeProductosAEntregar' 	=> $datosAndreani['detalle_productos'],
					'DetalleDeProductosARetirar' 	=> $datosAndreani['detalle_productos'],
					'Volumen' 						=> $datosAndreani['volumen'],
					'ValorDeclaradoConIva' 			=> $datosAndreani['valor_declarado'],
					'Contrato' 						=> $datosAndreani['contrato'],
					'IdCliente' 					=> null,
					'SucursalDeRetiro' 				=> $datosAndreani['sucursal_retiro'],
					'SucursalDelCliente' 			=> null
				)));

			if($phpresponse->GenerarEnviosDeEntregaYRetiroConDatosDeImpresionResult->CodigoDeResultado)
			{
				$dataGuia['datosguia'] 		= $phpresponse;
				$dataGuia['lastrequest'] 	= $datosAndreani;
				$serialJson 				= serialize(json_encode($dataGuia));
				$object->setData('andreani_datos_guia', $serialJson);

				$shipment 	= $observer->getEvent()->getShipment();
				$track = Mage::getModel('sales/order_shipment_track')
					->setNumber($phpresponse->GenerarEnviosDeEntregaYRetiroConDatosDeImpresionResult->NumeroAndreani)
					->setCarrierCode('andreani')//carrier code
					->setTitle('Andreani');
				$shipment->addTrack($track);

				$id = intval($datosAndreani["id"]);
				Mage::getModel('andreani/order')->load($id)->setData('cod_tracking',$phpresponse->GenerarEnviosDeEntregaYRetiroConDatosDeImpresionResult->NumeroAndreani)->save();
				Mage::getModel('andreani/order')->load($id)->setData('estado','Enviado')->save();
			}
			else
			{
				$error = 'Código de error '.$phpresponse->GenerarEnviosDeEntregaYRetiroConDatosDeImpresionResult->CodigoDeResultado.
					'al guardar los datos de la guía: '.$phpresponse->GenerarEnviosDeEntregaYRetiroConDatosDeImpresionResult->DescripcionDeResultado;
				return Mage::getSingleton('core/session')->addError($error);
			}
		} catch (SoapFault $e) {
			Mage::log("Error: " . $e);
			Mage::throwException(Mage::helper('andreani')->__('Algo ha ido mal con la conexión a Andreani. Intente nuevamente. (envío no generado).'));

		}
	}

	/**
	 * @description agrega el botón de generar guía, siempre y cuando
	 * haya creado un envío para ese orden, y dicho envío tenga datos del WS de Andreani
	 * en la DB.
	 * @param $observer
	 */
	public function adminhtmlWidgetContainerHtmlBefore($observer)
	{
		$block = $observer->getBlock();

		if($block->getOrder())
		{
			$shipments = $block->getOrder()->getShipmentsCollection()->getItems();
			$isAndreaniDatosGuia = false;
			$shipmentId			 = '';

			//Recorre para verificar si la orden tiene envíos hechos
			foreach ($shipments AS $shipment)
			{
				if(!empty($shipment))
				{
					//Si el envío está hecho y hay datos de andreani en la DB para generar
					//la guía, "setea" el "flag" en true para que se muestre el botón de generación
					//de la guía.
					if($shipment->getAndreaniDatosGuia()!='')
						$isAndreaniDatosGuia 	= true;
					$shipmentId 			= $shipment->getEntityId();
				}

			}

			//Verifica que el "flag" esté en true para mostrar el botón
			if($isAndreaniDatosGuia)
			{
				if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {
					$url = Mage::getUrl('andreani/generarguia/index', array('shipment' => $shipmentId));
					$block->addButton('generar_guia_andreani', array(
						'label'     => Mage::helper('andreani')->__('Generar Guía Andreani'),
						'onclick'   => "setLocation('{$url}','_blank')",
						'class'     => 'go'
					));
				}
			}

		}
	}

	/**
	 * Agregar massAction al sales_order
	 */
	public function addMassAction($observer) {
		$block = $observer->getEvent()->getBlock();
		if(($block instanceof Mage_Adminhtml_Block_Widget_Grid_Massaction || $block instanceof Enterprise_SalesArchive_Block_Adminhtml_Sales_Order_Grid_Massaction)
			&& $block->getRequest()->getControllerName() == 'sales_order')
		{
			/**
			 * ANDREANI-10
			 * Se cambio la url adnreani/adminhtml_orders/impandreani por adminhtml/orders/impandreani
			 * La url anterior me mandaba a la pagina 404 de magento
			 */
			$block->addItem('andreani', array(
				'label' => 'Imponer en Andreani',
				'url' => $block->getUrl('adminhtml/orders/impandreani'),
				'confirm' => Mage::helper('sales')->__('Desea imponer las ordenes en Andreani?')
			));
		}
	}

	/**
	 * @description: se encarga de setear el valor del envío.
	 */
	public function saveShippingMethodSetShippingAmount(Varien_Event_Observer $observer)
	{
		$params = Mage::app()->getRequest()->getParams();
		$isCacheWs = Mage::getStoreConfig('carriers/andreaniconfig/ws_cache',Mage::app()->getStore());
		$sucursalAndreaniSession = Mage::getSingleton('core/session')->getAndreaniSucursal();


		if ($params['shipping_method'] == "andreanisucursal_andreanisucursal" && $isCacheWs) {

			$price			= $params['shipping_amount'];
			$description	= $params['shipping_sucursal'];
			Mage::getSingleton('core/session')->setData('shipping_amount', $price);
			Mage::getSingleton('core/session')->setData('shipping_description', $description);

			$quote	 = Mage::getModel('checkout/cart')->getQuote();
			$address = $quote->getShippingAddress();
			$address->setShippingAmount($price);
			$address->setBaseShippingAmount($price);
			$address->setShippingDescription($description);
			$address->setSucursal(intval($params['andreani-sucursal']));

			$rates = $address->collectShippingRates()->getGroupedAllShippingRates();
			foreach ($rates as $carrier) {
				foreach ($carrier as $rate) {
					if($rate->getCode() == "andreanisucursal_andreanisucursal") {
						$rate->setMethodTitle($description);
						$rate->setPrice($price);
						$rate->save();
					}
				}
			}

			$address->setCollectShippingRates(false);
			$address->save();

			$billingAddress = $quote->getBillingAddress();
			$billingAddress->setSucursal(intval($params['andreani-sucursal']));
			$billingAddress->save();

		}else{
			$quote	 = Mage::getModel('checkout/cart')->getQuote();
			$address = $quote->getShippingAddress();
			$address->setSucursal(null);
			$address->save();

			$billingAddress = $quote->getBillingAddress();
			$billingAddress->setSucursal(null);
			$billingAddress->save();
		}


	}

	/**
	 * @description: se encarga de setear el valor del envío.
	 */
	public function estimateUpdatePostSetShippingAmount(Varien_Event_Observer $observer)
	{
		$params = Mage::app()->getRequest()->getParams();
		$isCacheWs = Mage::getStoreConfig('carriers/andreaniconfig/ws_cache',Mage::app()->getStore());
		$sucursalAndreaniSession = Mage::getSingleton('core/session')->getAndreaniSucursal();

		if ($params['estimate_method'] == "andreanisucursal_andreanisucursal" && $isCacheWs)
		{
			$price			= $params['shipping_amount'];
			$description	= $params['shipping_sucursal'];
			Mage::getSingleton('core/session')->setData('shipping_amount', $price);
			Mage::getSingleton('core/session')->setData('shipping_description', $description);

			$quote	 = Mage::getModel('checkout/cart')->getQuote();
			$address = $quote->getShippingAddress();

			$rates = $address->collectShippingRates()->getGroupedAllShippingRates();

			$address->setSucursal(intval($params['andreani-sucursal']));

			$address->save();

			$billingAddress = $quote->getBillingAddress();
			$billingAddress->setSucursal(intval($params['andreani-sucursal']));
			$billingAddress->save();
			/*foreach ($rates as $carrier) {
				foreach ($carrier as $rate) {
					if($rate->getCode() == 'andreanisucursal_andreanisucursal')
					{
						$rate->setMethodTitle($description);
						$rate->setPrice($price);
						$rate->save();
					}
				}
			}*/

			//$address->setCollectShippingRates(false);
			//$address->save();



		}else{
			$quote	 = Mage::getModel('checkout/cart')->getQuote();
			$address = $quote->getShippingAddress();
			$address->setSucursal(null);
			$address->save();

			$billingAddress = $quote->getBillingAddress();
			$billingAddress->setSucursal(null);
			$billingAddress->save();
		}

	}
	/**
	 * @description: se encarga de "setear" los valores del "fee" cuando se está operando
	 * con la caché del WS
	 */
	public function salesQuoteCollectTotalsBefore(Varien_Event_Observer $observer)
	{

		$isCacheWs = Mage::getStoreConfig('carriers/andreaniconfig/ws_cache',Mage::app()->getStore());
		$params = Mage::app()->getRequest()->getParams();

		/** @var Mage_Sales_Model_Quote $quote */
		$quote = $observer->getQuote();
		$address = $quote->getShippingAddress();

		if($address->getShippingAmount() == '')
		{
			$newHandlingFee = Mage::getSingleton('core/session')->getData('shipping_amount');
		}
		else
		{
			$newHandlingFee = $address->getShippingAmount();
		}

		$store    = Mage::app()->getStore($quote->getStoreId());
		$carriers = Mage::getStoreConfig('carriers', $store);
		foreach ($carriers as $carrierCode => $carrierConfig) {
			if($carrierCode == 'andreanisucursal' && $isCacheWs){
				$store->setConfig("carriers/{$carrierCode}/handling_type", 'F');
				$store->setConfig("carriers/{$carrierCode}/handling_fee", $newHandlingFee);
			}
		}

	}

	/**
	 * @description limpia las variables en sesion y los rates luego de generar la orden
	 */
	public function cleanAndreaniVars(Varien_Event_Observer $observer)
	{
		Mage::getSingleton('core/session')->unsAndreaniSucursal();

		$quote	 = Mage::getModel('checkout/cart')->getQuote();
		$address = $quote->getShippingAddress();
		$rates = $address->collectShippingRates()->getGroupedAllShippingRates();
		foreach ($rates as $carrier) {
			foreach ($carrier as $rate) {

				if($rate->getCode() == 'andreanisucursal_andreanisucursal')
				{
					$rate->setMethodTitle('Andreani Sucursal');
					$rate->setPrice(0);
					$rate->save();
				}
			}
		}

		$address->setCollectShippingRates(false);
		Mage::getSingleton('core/session')->setData('shipping_description','Andreani Sucursal');
		$address->save();
	}
	/**
	 * @description flag para mostrar el bloque de sucursales en el carrito.
	 */
	public function estimatePostSetShippingAmount()
	{
		Mage::getSingleton('core/session')->setEstimatePost(1);
	}
}
?>