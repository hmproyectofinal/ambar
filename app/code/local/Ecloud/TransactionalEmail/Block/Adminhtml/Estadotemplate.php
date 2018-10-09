<?php
class Ecloud_TransactionalEmail_Block_Adminhtml_Estadotemplate extends Mage_Adminhtml_Block_Widget_Grid_Container{
	public function __construct() {
		$this->_controller = "adminhtml_estadotemplate";
		$this->_blockGroup = "transactionalemail";
		$this->_headerText = Mage::helper("transactionalemail")->__("Administrador de Templates para estado");
		$this->_addButtonLabel = Mage::helper("transactionalemail")->__("Agregar nuevo template para estado");
		parent::__construct();
	}
}