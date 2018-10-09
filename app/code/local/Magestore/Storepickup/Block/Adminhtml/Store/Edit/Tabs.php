<?php

class Magestore_Storepickup_Block_Adminhtml_Store_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('storepickup_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('storepickup')->__('Store Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('storepickup')->__('General Information'),
            'title' => Mage::helper('storepickup')->__('General Information'),
            'content' => $this->getLayout()->createBlock('storepickup/adminhtml_store_edit_tab_form')->toHtml(),
        ));
        /*Edit by Son*/
//        if ($this->getRequest()->getParam('id')) {
//            //if(Mage::getStoreConfig('carriers/storepickup/active_gapi'))
//            $this->addTab('gmap_section', array(
//                'label' => Mage::helper('storepickup')->__('Google Map'),
//                'title' => Mage::helper('storepickup')->__('Google Map'),
//                'content' => $this->getLayout()->createBlock('storepickup/adminhtml_store_edit_tab_gmap')->toHtml(),
//            ));
//        }
//        $this->addTab('contact_section', array(
//            'label' => Mage::helper('storepickup')->__('Contact Information'),
//            'title' => Mage::helper('storepickup')->__('Contact Information'),
//            'content' => $this->getLayout()->createBlock('storepickup/adminhtml_store_edit_tab_contactform')->toHtml(),
//        ));
        /*End by Son*/
        $this->addTab('timeschedule_section', array(
            'label' => Mage::helper('storepickup')->__('Time Schedule'),
            'title' => Mage::helper('storepickup')->__('Time Schedule'),
            'content' => $this->getLayout()->createBlock('storepickup/adminhtml_store_edit_tab_timeschedule')->toHtml(),
        ));
        $this->addTab('message_section', array(
            'label' => Mage::helper('storepickup')->__('Customer Messages'),
            'title' => Mage::helper('storepickup')->__('Customer Messages'),
            'url' => $this->getUrl('*/*/message', array('_current' => true)),
            'class' => 'ajax',
        ));
        if ($this->getRequest()->getParam('id')) {
            $this->addTab('relatedorders_section', array(
                'label' => Mage::helper('storepickup')->__('Related Orders'),
                'url' => $this->getUrl('*/*/relatedorders', array('_current' => true, 'id' => $this->getRequest()->getParam('id'))),
                'class' => 'ajax',
            ));
        }

        return parent::_beforeToHtml();
    }

}