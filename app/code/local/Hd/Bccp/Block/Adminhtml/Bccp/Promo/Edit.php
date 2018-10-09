<?php

class Hd_Bccp_Block_Adminhtml_Bccp_Promo_Edit 
    extends Mage_Adminhtml_Block_Widget_Form_Container
{

    protected $_objectId    = 'id';
    protected $_blockGroup  = 'hd_bccp';
    protected $_controller  = 'adminhtml_bccp_promo';
    
    public function __construct()
    {
        parent::__construct();
        $_helper = Mage::helper('hd_bccp');
        $this->_updateButton('save', 'label', $this->__('Save Promo'));
        $this->_updateButton('delete', 'label', $this->__('Delete Promo'));
        if ($this->_getModel()->getId()) {
            $this->_addButton('saveandcontinue', array(
                    'label'     => Mage::helper('adminhtml')->__('Save and Continue Edit'),
                    'onclick'   => 'saveAndContinueEdit(\''.$this->_getSaveAndContinueUrl().'\')',
                    'class'     => 'save',
            ), -100);
        }
    }
    
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'   => true,
            'back'       => 'edit',
            'active_tab' => '{{tab_id}}'
        ));
    }
    
    protected function _prepareLayout()
    {
        $tabsBlockJsObject = 'promo_edit_tabsJsTabs';
        $tabsBlockPrefix   = 'promo_edit_tabs_';
        
        $this->_formScripts[] = "
            function saveAndContinueEdit(urlTemplate) {
                var tabsIdValue = " . $tabsBlockJsObject . ".activeTab.id;
                var tabsBlockPrefix = '" . $tabsBlockPrefix . "';
                if (tabsIdValue.startsWith(tabsBlockPrefix)) {
                    tabsIdValue = tabsIdValue.substr(tabsBlockPrefix.length)
                }
                var template = new Template(urlTemplate, /(^|.|\\r|\\n)({{(\w+)}})/);
                var url = template.evaluate({tab_id:tabsIdValue});
                editForm.submit(url);
            }
        ";
        return parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        $model = $this->_getModel();
        if ($model && $model->getId()) {
            return Mage::helper('hd_bccp')->__('Editing Promo "%s"', $model->getName());
        } else {
            return Mage::helper('hd_bccp')->__('New Promo');
        }
    }
    
    /**
     * @return Hd_Bccp_Model_Promo
     */
    protected function _getModel()
    {
        return Mage::registry(Hd_Bccp_Block_Adminhtml_Bccp_Promo::REGISTRY_MODEL_NAMESPACE);
    }

}
