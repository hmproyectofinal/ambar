<?php
class Ids_Andreani_Block_Adminhtml_Pedidos extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'andreani';
        $this->_controller = 'adminhtml_pedidos';
        $this->_headerText = Mage::helper('adminhtml')->__('Estado de Pedidos de Andreani');
 
        parent::__construct();
        $this->_removeButton('add');
    }

}
?>
