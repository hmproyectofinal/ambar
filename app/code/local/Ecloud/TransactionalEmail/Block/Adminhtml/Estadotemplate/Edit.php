<?php

class Ecloud_TransactionalEmail_Block_Adminhtml_Estadotemplate_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct() {
		parent::__construct();
		$this->_objectId = "id";
		$this->_blockGroup = "transactionalemail";
		$this->_controller = "adminhtml_estadotemplate";
		$this->_updateButton("save", "label", Mage::helper("transactionalemail")->__("Guardar"));
		$this->_updateButton("delete", "label", Mage::helper("transactionalemail")->__("Eliminar"));

		$this->_addButton("saveandcontinue", array(
			"label"     => Mage::helper("transactionalemail")->__("Guardar y continuar editando"),
			"onclick"   => "saveAndContinueEdit()",
			"class"     => "save",
			), -100);

		$this->_formScripts[] = "
		function saveAndContinueEdit(){
			editForm.submit($('edit_form').action+'back/edit/');
		}
		";
	}

	public function getHeaderText() {
		if( Mage::registry("estadotemplate_data") && Mage::registry("estadotemplate_data")->getId() ){
			return Mage::helper("transactionalemail")->__("Editar Template para estado de ID: '%s'", $this->htmlEscape(Mage::registry("estadotemplate_data")->getId()));
		} else{
			return Mage::helper("transactionalemail")->__("Agregar nuevo template para estado");
		}
	}
}