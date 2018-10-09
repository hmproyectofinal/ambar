<?php  

class Todopago_Modulodepago2_Block_Adminhtml_Formulariocustom extends Mage_Adminhtml_Block_Template{

	public function getCodigoFormulariocustom(){
		$formulario = file_get_contents(Mage::getBaseDir('design').'/frontend/base/default/template/modulodepago2/formulariocustom.phtml');		
		return $formulario;		
	}

	public function getCodigoFormulariocustomCss(){
		return file_get_contents(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN)."frontend/base/default/css/modulodepago2/formulariocustom.css");
	}
	
	public function getCodigoFormulariocustomJs(){
		return TRUE;
	}

	public function getActionForm(){
		return Mage::helper("adminhtml")->getUrl("modulodepago2/adminhtml_formulariocustom/modificarcss");
	}


}