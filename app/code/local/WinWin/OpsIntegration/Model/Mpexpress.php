<?php

class WinWin_OpsIntegration_Model_Mpexpress
{
	
	public function getInteres($order = null)
	{
		return 0;
	}
	
	public function getCuotas($order = null)
	{
		return 1;
	}
	
	public function getTarjeta($order = null)
	{
		return '';
	}

	public function getBanco($order = null)
	{
		return '';
	}

	public function getAuth($order = null)
	{
		return $order->getIncrementId();
	}

	public function getBin($order = null)
	{
		return '';
	}

	

	

	
}
