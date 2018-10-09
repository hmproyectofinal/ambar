<?php
class Ecloud_TransactionalEmail_Block_Adminhtml_Estadotemplate_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm() {
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset("transactionalemail_form", array("legend"=>Mage::helper("transactionalemail")->__("Template de Email de estado")));

		$fieldset->addField("estado_code", "select", array(
			"label" => Mage::helper("transactionalemail")->__("Codigo del estado"),					
			"class" => "required-entry",
			"required" => true,
			"name" => "estado_code",
			"values" => Mage::helper("transactionalemail")->getEstados()
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


		if (Mage::getSingleton("adminhtml/session")->getEstadotemplateData())
		{
			$form->setValues(Mage::getSingleton("adminhtml/session")->getEstadotemplateData());
			Mage::getSingleton("adminhtml/session")->setEstadotemplateData(null);
		} 
		elseif(Mage::registry("estadotemplate_data")) {
			$form->setValues(Mage::registry("estadotemplate_data")->getData());
		}
		return parent::_prepareForm();
	}
}
