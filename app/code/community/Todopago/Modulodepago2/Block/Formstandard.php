<?php
class Todopago_Modulodepago2_Block_Formstandard extends Mage_Payment_Block_Form{
	protected function _construct(){
		parent::_construct ();
		
		$this->setTemplate ( 'todopagomodulodepago/standard_form.phtml' );
	}
	
}