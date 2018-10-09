<?php
class Ecloud_TransactionalEmail_Block_Adminhtml_Carriertemplate extends Mage_Adminhtml_Block_Widget_Grid_Container{
	public function __construct() {
		$this->_controller = "adminhtml_carriertemplate";
		$this->_blockGroup = "transactionalemail";
		$this->_headerText = Mage::helper("transactionalemail")->__("Administrador de Templates para Carrier");
		$this->_addButtonLabel = Mage::helper("transactionalemail")->__("Agregar nuevo template para Carrier");
		parent::__construct();
	}
}