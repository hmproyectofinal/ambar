<?php
class Ecloud_TransactionalEmail_Model_Observer extends Mage_Core_Model_Session_Abstract {
	public function enviarEmailTemplate($observer) {

		//Mage::log("Cambio de estado OBSERVER - Evento: sales_order_save_after");
		
		$order = $observer->getEvent()->getOrder();
		//Mage::log("Order ID: ".$order->getIncrementId());
		$storeId = $order->getStore()->getId();
		//Mage::log("Store ID: ".$storeId);

		if(Mage::getStoreConfig('transactionalemail/global/activado', $order->getStore()) ){

			$status = $order->getStatus();
			//Mage::log("Status: ".$status);

			$estadoTemplate = Mage::getModel("transactionalemail/estadotemplate")->loadByEstado($status, $storeId);

			$templateId = $estadoTemplate->getEmailTemplate();
			//Mage::log("ID de template: ".$templateId);

			if($templateId != "") {			
				if ($order->getCustomerIsGuest()) {
					//Mage::log("Cliente invitado");
		            $customerName = $order->getBillingAddress()->getName();
		            $customerEmail = $order->getBillingAddress()->getEmail();
		        } else {
		        	//Mage::log("Cliente registrado");
		            $customerName = $order->getCustomerName();
		            $customerEmail = $order->getCustomerEmail();
		        }
		        //Mage::log("Datos del cliente: ".$customerName." (".$customerEmail.")");

		        $senderName = Mage::getStoreConfig('trans_email/ident_sales/name');
				$senderEmail = Mage::getStoreConfig('trans_email/ident_sales/email');
		        $sender = array('name' => $senderName,'email' => $senderEmail);
				//$storeId = Mage::app()->getStore()->getId();
				//$translate  = Mage::getSingleton('core/translate');
				$var = array('orderId' => $order->getIncrementId(), 'order' => $order);
				//Mage::log("Order ID: ".$var['orderId']);
				Mage::getModel('core/email_template')->sendTransactional($templateId, $sender, $customerEmail, $customerName, $var, $storeId);
		        //Mage::log("Email enviado");
			}
		}
	}
}