<?php

class WinWin_OpsIntegration_Block_Adminhtml_Execution_History_Info_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('integrations_form', array('legend'=>Mage::helper('winwin_opsintegration')->__('Execution History Information')));

		$fieldset->addField('id', 'label', array(
			'label'		=> Mage::helper('winwin_opsintegration')->__('ID:'),
			'name'		=> 'id',
		));

		$fieldset->addField('date', 'label', array(
			'label'		=> Mage::helper('winwin_opsintegration')->__('Date:'),
			'name'		=> 'date',
		));

		$fieldset->addField('time', 'label', array(
			'label'		=> Mage::helper('winwin_opsintegration')->__('Time:'),
			'name'		=> 'time',
		));

//        $fieldset->addField('file_name', 'link', array(
//            'label'     => Mage::helper('winwin_integration')->__('Processed file:'),
//            'name'      => 'file_name',
//			'href'		=> Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).$this->getSingleValue('file_name'),
//			'note'		=> $this->generateComment('file_name'),
//        ));
//
//		$fieldset->addField('correct_records', 'label', array(
//			'label'		=> Mage::helper('winwin_integration')->__('Records processed correctly:'),
//			'name'		=> 'correct_records',
//		));
//
//		$fieldset->addField('total_records', 'label', array(
//			'label'		=> Mage::helper('winwin_integration')->__('Total records:'),
//			'name'		=> 'total_records',
//		));
//
//		$fieldset->addField('exec_type', 'label', array(
//			'label'		=> Mage::helper('winwin_integration')->__('Execution type:'),
//			'name'		=> 'exec_type',
//		));
//
//		$value_registry = Mage::registry('integrations_data')->getData();
//
//		if ($value_registry['exec_type'] == 'manual') {
//			$fieldset->addField('username', 'label', array(
//				'label'		=> Mage::helper('winwin_integration')->__('User name:'),
//				'name'		=> 'username',
//			));
//		}
//
//		$fieldset->addField('status', 'label', array(
//			'label'		=> Mage::helper('winwin_integration')->__('Status:'),
//			'name'		=> 'status',
//		));
//
//		if (!($value_registry['error_log_link'] == NULL)){
//			$fieldset->addField('error_log_link', 'link', array(
//				'label'		=> Mage::helper('winwin_integration')->__('Error log file: '),
//				'name'		=> 'error_log_link',
//				'href'		=> $this->avoidNull('error_log_link'),
//				'note'		=> $this->generateComment('error_log_link'),
//			));
//		}
//
//        $fieldset->addField('exec_log_link', 'link', array(
//            'label'     => Mage::helper('winwin_integration')->__('Execution log file:'),
//            'name'      => 'exec_log_link',
//			'href'		=> Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).$this->getSingleValue('exec_log_link'),
//			'note'		=> $this->generateComment('exec_log_link'),
//        ));
//
		$form->setValues(Mage::registry('integrations_data')->getData());

        if ( Mage::getSingleton('adminhtml/session')->getIntegrationsData() )
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getIntegrationsData());
            Mage::getSingleton('adminhtml/session')->setIntegrationsData(null);
        } elseif ( Mage::registry('integrations_data') ) {
            $form->setValues(Mage::registry('integrations_data')->getData());
        }
        return parent::_prepareForm();
    }

	protected function getSingleValue($field_name)
	{
		$values_array = array();
		
        if (Mage::registry('integrations_data')) {
            $values_array = Mage::registry('integrations_data')->getData();
            return $values_array[$field_name];
        }

        return $values_array;

	}

	protected function avoidNull($param)
	{
		if ($this->getSingleValue($param) == NULL) {return 'file not available';}
		else return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).$this->getSingleValue($param);
	}

	protected function generateComment($param)
	{
		if ($this->getSingleValue($param) == NULL) {
			return '';
		}
		else
		{
			return Mage::helper('winwin_opsintegration')->__('To save the file on your computer, right click on the link and choose "Save as..."');
		}
	}
}