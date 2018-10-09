<?php
class Todopago_Modulodepago2_Model_System_Config_Source_Testproduccion {
	

	public function toOptionArray() {
		return array (
				
				array (
						//cambiar el value a test
						'value' => 'test',
						'label' => 'developers' 
				),
				array (
						'value' => 'produccion',
						'label' => 'Producción' 
				),
				
		);
	}
}