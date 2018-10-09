<?php

class Hd_Bccp_Block_Adminhtml_Bccp_Bank_Edit_Tab_Creditcard extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setDestElementId('edit_form');
    }

    protected function _prepareForm()
    {
        $_helper = Mage::helper('hd_bccp');
        
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('bank_creditcard_form', array(
            'legend' => $this->__('Asigned Credit Cards')
        ));
        
        $storeIds = ($_helper->isStoreSupportEnable()) 
            ? $this->getModel()->getStoreIds() : null;

        $creditcards = Mage::getSingleton('hd_bccp/system_config_source_creditcard')
            ->toOptionArray(false, null, $storeIds);
        
        $fieldset->addField('creditcard_ids', 'multiselect', array(
            'name' => 'creditcard_ids',
            'label' => $this->__('Credit Cards'),
            'values' => $creditcards,
//            'options' => $creditcards,
        ));
        
        $form->setValues($this->getModel());
        $this->setForm($form);
        return parent::_prepareForm();
    }

}