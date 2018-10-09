<?php
class Todopago_Modulodepago2_Model_System_Config_Source_Csverticales {
	

	public function toOptionArray() {
		return array (
				
				array (
						'value' =>  Todopago_Modulodepago2_Model_Cybersource_Factorycybersource::RETAIL ,
						'label' => 'Retail' 
				),
// 				array (
// 						'value' => 'Travel',
// 						'label' => 'Travel' 
// 				),
				array (
						'value' => Todopago_Modulodepago2_Model_Cybersource_Factorycybersource::SERVICE,
						'label' => 'Services' 
				),
				array (
						'value' => Todopago_Modulodepago2_Model_Cybersource_Factorycybersource::DIGITAL_GOODS ,
						'label' => 'Digital Goods' 
				),
				array (
						'value' => Todopago_Modulodepago2_Model_Cybersource_Factorycybersource::TICKETING,
						'label' => 'Ticketing' 
				) ,
		);
	}
}
