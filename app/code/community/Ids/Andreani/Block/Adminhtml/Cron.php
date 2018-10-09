<?php
class Ids_Andreani_Block_Adminhtml_Cron extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'andreani';
        $this->_controller = 'adminhtml_cron';
        $this->_headerText = Mage::helper('adminhtml')->__('Json Sucursales');

        parent::__construct();
        $this->_removeButton('add');
    }

}

