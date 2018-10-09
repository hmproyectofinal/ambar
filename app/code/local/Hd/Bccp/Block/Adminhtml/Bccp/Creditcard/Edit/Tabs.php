<?php

class Hd_Bccp_Block_Adminhtml_Bccp_Creditcard_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('creditcard_edit_tabs')
            ->setDestElementId('edit_form')
            ->setTitle(Mage::helper('hd_bccp')->__('Manage Credit Cards'));
    }

    protected function _beforeToHtml()
    {
        $id = Mage::app()->getRequest()->getParam('id');
        $this->addTab('main_section', array(
            'label'     => Mage::helper('hd_bccp')->__('Credit Card Information'),
            'title'     => Mage::helper('hd_bccp')->__('Credit Card Information'),
            'content'   => $this->getLayout()
                ->createBlock('hd_bccp/adminhtml_bccp_creditcard_edit_tab_main', 'creditcard_main_form')
                ->setModel($this->_getModel())
                ->toHtml(),
        ));
        if ($id) {
            $this->addTab('payment_section', array(
                'label'     => Mage::helper('hd_bccp')->__('Payments'),
                'title'     => Mage::helper('hd_bccp')->__('Payments'),
                'content'   => $this->getLayout()
                    ->createBlock('hd_bccp/adminhtml_bccp_creditcard_edit_tab_payment', 'creditcard_payment_form')
                    ->setModel($this->_getModel())
                    ->toHtml(),
            ));
        }
        return parent::_beforeToHtml();
    }
    
    /**
     * @return Hd_Bccp_Model_Creditcard
     */
    protected function _getModel()
    {
        return Mage::registry(Hd_Bccp_Block_Adminhtml_Bccp_Creditcard::REGISTRY_MODEL_NAMESPACE);
    }
}