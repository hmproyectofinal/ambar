<?php
class Hd_Bccp_Block_Adminhtml_Bccp_Creditcard 
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    const REGISTRY_MODEL_NAMESPACE  = "hd_bccp_creditcard_data";
    
    protected $_blockGroup          = 'hd_bccp';
    
    protected $_controller          = 'adminhtml_bccp_creditcard';
    
    public function __construct()
    {
        parent::__construct();
        $this->_updateButton('add', 'label', Mage::helper('hd_bccp')->__('Add New Credit Card'));
        $this->_headerText = Mage::helper('hd_bccp')->__('Manage Credit Cards');
    }
}