<?php

class Hd_Bccp_Block_Adminhtml_Bccp_Bank_Edit_Tabs 
    extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('bank_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('hd_bccp')->__('Manage Banks'));
    }
    
    protected function _beforeToHtml()
    {
        $id = Mage::app()->getRequest()->getParam('id');
        $this->addTab('main_section', array(
            'label'     => Mage::helper('hd_bccp')->__('Bank Information'),
            'title'     => Mage::helper('hd_bccp')->__('Bank Information'),
            'content'   => $this->getLayout()
                ->createBlock('hd_bccp/adminhtml_bccp_bank_edit_tab_main', 'bank_main_form')
                ->setModel($this->_getModel())
                ->toHtml(),
        ));
        if ($id) {
            $this->addTab('creditcard_section', array(
                'label'     => Mage::helper('hd_bccp')->__('Credit Cards'),
                'title'     => Mage::helper('hd_bccp')->__('Credit Cards'),
                'content'   => $this->getLayout()
                    ->createBlock('hd_bccp/adminhtml_bccp_bank_edit_tab_creditcard','bank_creditcard_form')
                    ->setModel($this->_getModel())
                    ->toHtml(),
            ));
        }
        return parent::_beforeToHtml();
    }
    
    /**
     * @return Hd_Bccp_Model_Bank
     */
    protected function _getModel()
    {
        return Mage::registry(Hd_Bccp_Block_Adminhtml_Bccp_Bank::REGISTRY_MODEL_NAMESPACE);
    }
    
}