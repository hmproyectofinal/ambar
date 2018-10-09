<?php
class Todopago_Modulodepago2_Adminhtml_FormulariocustomController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Customización de Formulario de Pago"));
	   $this->renderLayout();
    }

    public function modificarcssAction(){
    	$css_formulario = $_POST['css_formulariocustom'];
    	$html_formulario = $_POST['html_formulariocustom'];	
    	$css = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_SKIN)."/frontend/base/default/css/modulodepago2/formulariocustom.css";
    	$archivo_css = fopen($css, 'w');
    	fwrite($archivo_css, $css_formulario);
    	fclose($archivo_css);
    	$html = Mage::getBaseDir('design').'/frontend/base/default/template/modulodepago2/formulariocustom.phtml';
    	
    	$archivo_html = fopen($html, 'w');
    	fwrite($archivo_html, $html_formulario);
    	fclose($archivo_html);
    	$this->_redirect('*/*/index');
    }
}
