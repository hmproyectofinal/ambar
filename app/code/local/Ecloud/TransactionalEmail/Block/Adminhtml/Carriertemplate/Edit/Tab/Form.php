<?php
class Ecloud_TransactionalEmail_Block_Adminhtml_Carriertemplate_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm() {
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset("transactionalemail_form", array("legend"=>Mage::helper("transactionalemail")->__("Template de Email de Carrier")));

		$fieldset->addField("carrier_code", "select", array(
			"label" => Mage::helper("transactionalemail")->__("Codigo del Carrier"),					
			"class" => "required-entry",
			"required" => true,
			"name" => "carrier_code",
			"values" => Mage::helper("transactionalemail")->getCarriers()
			));

		$fieldset->addField("email_template", "select", array(
			"label" => Mage::helper("transactionalemail")->__("Template del Email"),					
			"class" => "required-entry",
			"required" => true,
			"name" => "email_template",
			"values" => Mage::helper("transactionalemail")->getTemplates()
			));

		//if (!Mage::app()->isSingleStoreMode()) {
		    $fieldset->addField('store_id', 'multiselect', array(
		        'name' => 'stores[]',
		        'label' => Mage::helper('transactionalemail')->__('Store View'),
		        'title' => Mage::helper('transactionalemail')->__('Store View'),
		        'required' => true,
		        'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
		    ));
		/*}else{
		    $fieldset->addField('store_id', 'hidden', array(
		        'name' => 'stores[]',
		        'value' => Mage::app()->getStore(true)->getId()
		    ));
		}*/


		if (Mage::getSingleton("adminhtml/session")->getCarriertemplateData())
		{
			$form->setValues(Mage::getSingleton("adminhtml/session")->getCarriertemplateData());
			Mage::getSingleton("adminhtml/session")->setCarriertemplateData(null);
		} 
		elseif(Mage::registry("carriertemplate_data")) {
			$form->setValues(Mage::registry("carriertemplate_data")->getData());
		}
		return parent::_prepareForm();
	}
}
