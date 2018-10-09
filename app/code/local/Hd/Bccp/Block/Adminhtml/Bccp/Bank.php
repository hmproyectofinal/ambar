<?php
class Hd_Bccp_Block_Adminhtml_Bccp_Bank 
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    const REGISTRY_MODEL_NAMESPACE  = "hd_bccp_bank_data";
    
    protected $_blockGroup          = 'hd_bccp';
    
    protected $_controller          = 'adminhtml_bccp_bank';
    
    public function __construct()
    {
        parent::__construct();
        $this->_updateButton('add', 'label', Mage::helper('hd_bccp')->__('Add New Bank'));
        $this->_headerText = Mage::helper('hd_bccp')->__('Manage Banks');
    }
}