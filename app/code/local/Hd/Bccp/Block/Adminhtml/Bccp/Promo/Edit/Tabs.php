<?php

class Hd_Bccp_Block_Adminhtml_Bccp_Promo_Edit_Tabs 
    extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('promo_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('hd_bccp')->__('Manage Promotions'));
    }
    
    protected function _beforeToHtml()
    {
        $id = Mage::app()->getRequest()->getParam('id');
        $this->addTab('main_section', array(
            'label'     => Mage::helper('hd_bccp')->__('Promo Information'),
            'title'     => Mage::helper('hd_bccp')->__('Promo Information'),
            'content'   => $this->getLayout()
                ->createBlock('hd_bccp/adminhtml_bccp_promo_edit_tab_main', 'promo_main_form')
                ->setModel($this->_getModel())
                ->toHtml(),
        ));
        if ($id) {
            $this->addTab('validity_section', array(
                'label'     => Mage::helper('hd_bccp')->__('Promo Validity'),
                'title'     => Mage::helper('hd_bccp')->__('Promo Validity'),
                'content'   => $this->getLayout()
                    ->createBlock('hd_bccp/adminhtml_bccp_promo_edit_tab_validity','promo_validity_form')
                    ->setModel($this->_getModel())
                    ->toHtml(),
            ));
//            $this->addTab('condition_section', array(
//                'label'     => Mage::helper('hd_bccp')->__('Promo Conditions'),
//                'title'     => Mage::helper('hd_bccp')->__('Promo Conditions'),
//                'content'   => $this->getLayout()
//                    ->createBlock('hd_bccp/adminhtml_bccp_promo_edit_tab_condition','promo_condition_form')
//                    ->setModel($this->_getModel())
//                    ->toHtml(),
//            ));
        }
        return parent::_beforeToHtml();
    }
    
    /**
     * @return Hd_Bccp_Model_Promo
     */
    protected function _getModel()
    {
        return Mage::registry(Hd_Bccp_Block_Adminhtml_Bccp_Promo::REGISTRY_MODEL_NAMESPACE);
    }
    
}