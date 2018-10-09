<?php

class Brandlive_MercadoPago_Model_Cron{

	const LOG_FILE = 'mercadopago-cancelacion.log';
	const MP_SEARCH_PAYMENT_OFFSET = 0;
	const MP_SEARCH_PAYMENT_LIMIT  = 20;

	public function cancelOrdersAction(){
		foreach (Mage::app()->getStores() as $store) {		
			Mage::helper('mercadopago')->log('WebsiteId: '.$store->getWebsiteId(), self::LOG_FILE);
			$this->_cancelOrders($store->getWebsiteId(), $store->getId());			
		}
	}

	protected function _cancelOrders($website, $store){

		Mage::app()->setCurrentStore($store);			
			
		$helper = Mage::helper('brandlive_mercadopago/data');
		if (!$helper->getCancelationCronEnabled($website) || !$helper->getExpiresEnabled($website)) return false;
		
		$expirationDuration = $helper->getExpirationDuration($website) + $helper->getCancelationCronExpiration($website);		

		$logEnabled = $helper->getCancelationCronLogEnabled($website);

		/* Format our dates */
		$date    				= Mage::getModel('core/date');
		$currentTimestamp    	= $date->timestamp(time());
		$expirationTimestamp 	= strtotime("-" . $expirationDuration .  " minutes", $currentTimestamp);
		$toDate 				= $date->gmtDate('Y-m-d H:i:s', $expirationTimestamp);

		$limit 	= $helper->getCancelationCronCollectionLimit($website);
					
		/* Get the collection */
		$orders = Mage::getModel('sales/order')->getCollection()				
				->join(
        				array('payment' => 'sales/order_payment'),
        					'main_table.entity_id = payment.parent_id', array('payment_method' => 'payment.method')
    				  )	
				->addAttributeToFilter('payment.method', array('eq' => 'mercadopago_standard'))
	    		->addAttributeToFilter('created_at', array('to' => $toDate))
	    		->addAttributeToFilter('store_id', array('eq' => $store))
	    		->addAttributeToFilter('status', array('in' => $helper->getCancelationCronOrderStatuses()))
	    		->addAttributeToSort('increment_id', 'DESC')
	    		->setPageSize($limit);

	    if ($logEnabled) Mage::helper('mercadopago')->log('Se encontraron '. count($orders) .' ordenes', self::LOG_FILE);
	    		
	    if ($orders->count() > 0){	    		    	
			
			$clientId 		= Mage::app()->getWebsite($website)->getConfig('payment/mercadopago_standard/client_id');
        	$clientSecret 	= Mage::app()->getWebsite($website)->getConfig('payment/mercadopago_standard/client_secret');

			$mp = Mage::helper('mercadopago')->getApiInstance($clientId, $clientSecret);

		   	foreach ($orders as $order) {		   	
		   		$incrementId = $order->getIncrementId();

		   		if ($logEnabled) Mage::helper('mercadopago')->log('Orden: '.$incrementId, self::LOG_FILE);	   
		   		$externalId  = $incrementId;

		   		$payments = null;
		   		
		   		try{

		   			$filters = array (
						"external_reference" => $externalId
					);

		   			$payments = $mp->search_payment($filters, self::MP_SEARCH_PAYMENT_OFFSET, self::MP_SEARCH_PAYMENT_LIMIT);

		   			if ($logEnabled) Mage::helper('mercadopago')->log('Response Payments: ' . print_r($payments,true), self::LOG_FILE);

		   		}catch (Exception $e) {
		   			if ($logEnabled) Mage::helper('mercadopago')->log('Error al obtener los pagos de la orden: ' . $incrementId . ' - ' . $e->getMessage(), self::LOG_FILE);
			    	continue;
		    	}

		    	//Si MP no me devolvio nada no toco la orden
		    	if ( $payments === null || $payments === false ){
					if ($logEnabled) Mage::helper('mercadopago')->log('MP no devolvio nada para la orden: ' . $incrementId . '. No hago nada en Magento.', self::LOG_FILE);
		    		continue;	
		    	} 

		    	//Si MP devolvio algo pero no hay transaccion asociada, cancelo la orden
		    	if ( count($payments['response']['results']) == 0 ){ 
		    		if ($logEnabled) Mage::helper('mercadopago')->log('No hay transaccion asociada, cancelo la orden', self::LOG_FILE);
		    		$this->_cancelOrder($order, $logEnabled);
		    		continue;
		    	}

		    	//Ordeno los pagos para obtener el ultimo
		    	$results = $payments['response']['results'];		    	

		    	if ( count($results) > 1 ){
			    	usort($results, function($a, $b) {
					   return strtotime($b['collection']['last_modified']) - strtotime($a['collection']['last_modified']);
					});
				}

				$last_payment = reset($results);

				//si el ultmo pago de la orden es cancelado, cancelo la orden.
				//si es aprobado, la apruebo
				if ($last_payment['collection']['status'] == 'cancelled'){
					if ($logEnabled) Mage::helper('mercadopago')->log('El ultimo estado de la orden en MP es cancelado, cancelo la orden en Magento', self::LOG_FILE);
					$this->_cancelOrder($order, $logEnabled);				
				}elseif ($last_payment['collection']['status'] == 'approved' && $last_payment['collection']['status_detail'] == 'accredited') {
					if ($logEnabled) Mage::helper('mercadopago')->log('El ultimo estado de la orden en MP es approved, apruebo la orden en Magento', self::LOG_FILE);
					$this->_aproveOrder($order, $logEnabled);
				}elseif ($logEnabled){
					if ($logEnabled) Mage::helper('mercadopago')->log('La orden tiene otro estado diferente a cancelled o approved. No modifico la orden ' . $incrementId . ' en Magento. Status: ' .  $last_payment['collection']['status'] . ' status_detail: ' . $last_payment['collection']['status_detail'], self::LOG_FILE);
				}	

		   	}
		   	
		}				
	}

	protected function _cancelOrder($order, $logEnabled){	

		$incrementId = $order->getIncrementId();

		if ($logEnabled) Mage::helper('mercadopago')->log('Orden a cancelar: ' . $incrementId , self::LOG_FILE);

		if ($order->canCancel()){
			try{
				//Copiado del método Cancel de la clase Order. Lo hago asi para poder enviar un comentario custom
				$order->getPayment()->cancel();
	            $order->registerCancellation('Orden cancelada por cron', true);						
				$order->save();					
				if ($logEnabled) Mage::helper('mercadopago')->log('Orden cancelada por cron: ' . $incrementId , self::LOG_FILE);
			}catch (Exception $e){
				if ($logEnabled) Mage::helper('mercadopago')->log('Error al cancelar la orden: ' . $incrementId . ' - ' . $e->getMessage(), self::LOG_FILE);
			}			
		}else{
			if ($logEnabled) Mage::helper('mercadopago')->log('La orden no puede ser cancelada: ' . $incrementId, self::LOG_FILE);
		}

	}	

	protected function _aproveOrder($order, $logEnabled){	
		
		$incrementId = $order->getIncrementId();

		if ($logEnabled) Mage::helper('mercadopago')->log('Orden a aprobar: ' . $incrementId , self::LOG_FILE);
		
		$status = Mage::getStoreConfig('payment/mercadopago/order_status_approved');
		$state  = Mage::getResourceModel('sales/order_status_collection')->joinStates()->addFieldToFilter('main_table.status', $status)->getFirstItem()->getState();                         
    	    			
		try{		
			$order->setState($state, $status, 'Orden aprobada por cron.', true);
			$order->save();
			$order->sendNewOrderEmail();
			if ($logEnabled) Mage::helper('mercadopago')->log('Orden aprobada por cron: ' . $incrementId , self::LOG_FILE);
		}catch (Exception $e){
			if ($logEnabled) Mage::helper('mercadopago')->log('Error al aprobar la orden: ' . $incrementId . ' - ' . $e->getMessage(), self::LOG_FILE);
		}
	}	

}