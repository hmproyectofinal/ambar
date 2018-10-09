<?php
class Ecloud_TransactionalEmail_Block_Adminhtml_Carriertemplate_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct() {
		parent::__construct();
		$this->setId("carriertemplate_tabs");
		$this->setDestElementId("edit_form");
		$this->setTitle(Mage::helper("transactionalemail")->__("Informacion del Template para el Carrier"));
	}

	protected function _beforeToHtml() {
		$this->addTab("form_section", array(
			"label" => Mage::helper("transactionalemail")->__("Informacion Basica"),
			"title" => Mage::helper("transactionalemail")->__("Informacion Basica"),
			"content" => $this->getLayout()->createBlock("transactionalemail/adminhtml_carriertemplate_edit_tab_form")->toHtml(),
			));
		return parent::_beforeToHtml();
	}
}
