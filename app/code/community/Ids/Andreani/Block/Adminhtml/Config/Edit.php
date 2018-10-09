<?php  
class Ids_Andreani_Block_Adminhtml_Config_Edit extends Mage_Adminhtml_Block_Widget_Form_Container{
    
	public function __construct(){
        parent::__construct();
        $this->_blockGroup = 'andreani';
        $this->_controller = 'adminhtml_config';
        $this->_updateButton('save', 'label', Mage::helper('andreani')->__('Save Changes'));
        $this->_removeButton('reset');
        $this->_removeButton('delete');
        $this->_removeButton('back');
    }

    public function getHeaderText(){
        return Mage::helper('andreani')->__('Andreani Configuration');
    }
	
}
?>