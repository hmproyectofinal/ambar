<?php

class Todopago_Modulodepago2_MenupaymentController extends Mage_Core_Controller_Front_Action{

	public function entidadSelectAction(){
		$tarjeta = $this->getRequest()->get('tarjeta');
		$wsdls = $this->get_wsdls();
		$http_header = $this->get_http_header();
		$end_point = $this->get_end_point();
		
		require_once(Mage::getBaseDir('lib') . '/metododepago2/TodoPago.php');
		$connector = new TodoPago($http_header, $wsdls, $end_point);
		
		$merchant = Mage::getStoreConfig('payment/todopago_modo/idstore');	
		
		try{
			$pay_methods = $connector->getAllPaymentMethods(array('MERCHANT'=>$merchant));
		}
		catch(Exception $e){
			$exception['Operations']['Exception']=$e;
			return $exception;
		}
		if(sizeof($pay_methods['PaymentMethod'][$tarjeta]['PromosCollection'])==0){
			echo "Cupón sin entidad Bancaria";
		}
		else if(sizeof($pay_methods['PaymentMethod'][$tarjeta]['PromosCollection']['Promo']['BanksCollection']['Bank'][0])==0){
		
		
			echo '<lable id="entidad_label">Entidad:</lable><br />';
			echo '<select id="bancos" name="payment[banco]">';
			echo '<option>'.$pay_methods['PaymentMethod'][$tarjeta]['PromosCollection']['Promo']['BanksCollection']['Bank']['Name'].'</option>';
		
			echo "</select>";
		}
			
		else{
			$bancos = $pay_methods['PaymentMethod'][$tarjeta]['PromosCollection']['Promo']['BanksCollection']['Bank'];
			echo '<lable id="entidad_label">Entidad:</lable><br />';
			echo '<select id="bancos" name="payment[banco]">';
				
			foreach($bancos as $value){
				echo '<option>'.$value['Name'].'</option>';
					
			}
			echo "</select>";
				
		}
		
			
			
        
	}

	public function cuotasSelectAction(){
		

		echo '<select id="cuotas" name="payment[cuotas]">';
		echo '<option value="false">Selecciones cuotas</option>';
		for ($i=0; $i<=12; $i++){
			echo "<option>".$i." cuotas</option>";
		}
		echo '</select>';
	}
	
private function get_http_header(){
	return json_decode(Mage::getStoreConfig('payment/modulodepago2/header_http'), TRUE);
}
	
private function get_end_point(){
	return Mage::getStoreConfig('payment/todopago_modo/todopago_end_point');
}
	
private function get_wsdls(){
	return json_decode(Mage::getStoreConfig('payment/todopago_modo/todopago_wsdl'), TRUE);
}

}
