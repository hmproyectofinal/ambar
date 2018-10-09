<?php   
class Todopago_Modulodepago2_Block_Formulariocustom extends Mage_Core_Block_Template{   

	public function get_amount(){
		return $this->getRequest()->get('amount');
	}

}