<?php

class WinWin_OpsIntegration_Model_Decidir
{
	
	
	
	public function getCuotas($order = null)
	{
		return $order->getPayment()->getData('hd_bccp_payments');
	}
	
	public function getTarjeta($order = null)
	{
		return $order->getPayment()->getData('hd_bccp_cc_name');
	}

	public function getBanco($order = null)
	{
		return $order->getPayment()->getData('hd_bccp_bank_name');
	}

	public function getAuth($order = null)
	{
		$payment =  $order->getPayment();		
		$data = unserialize($payment->getData('decidir_post_transaction_data'));
		return isset($data['Codautorizacion']) ? $data['Codautorizacion'] : '';
		
	}

	public function getBin($order = null)
	{
		$payment =  $order->getPayment();		
		$data = unserialize($payment->getData('decidir_post_transaction_data'));
		return isset($data['Nrotarjetavisible']) ? $data['Nrotarjetavisible'] : '';
	}

	public function getInteres($order = null)
	{
		$address = $order->getShippingAddress();		
		return $address->getData('hd_bccp_surcharge');
	}
	
}
