<?php
class Hd_Bccp_Block_Adminhtml_Rule_Widget_Chooser
    extends Mage_Adminhtml_Block_Widget_Grid
{
    
    protected $_entity;
    
    public function __construct($arguments=array())
    {
        parent::__construct($arguments);

        
        if ($this->getRequest()->getParam('current_grid_id')) {
            $this->setId($this->getRequest()->getParam('current_grid_id'));
        } else {
            $this->setId("{$this->_entity}ChooserGrid_".$this->getId());
        }
        
        $form = $this->getJsFormObject();
        $this->setRowClickCallback("$form.chooserGridRowClick.bind($form)")
            ->setCheckboxCheckCallback("$form.chooserGridCheckboxCheck.bind($form)")
            ->setRowInitCallback("$form.chooserGridRowInit.bind($form)")
            ->setDefaultSort('name')
            ->setUseAjax(true);
        
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
        
    }    

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == "in_{$this->_entity}") {
            $selected = $this->_getSelectedValues();
            if (empty($selected)) {
                $selected = '';
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('name', array('in'=> $selected));
            } else {
                $this->getCollection()->addFieldToFilter('name', array('nin'=> $selected));
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
    
    protected function _prepareCollection()
    {
        if($entity = $this->_entity) {
            $collection = Mage::getResourceModel("hd_bccp/{$entity}_collection");
            $this->setCollection($collection);
        }
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn("in_{$this->_entity}", array(
            'index'             => 'name',
            'name'              => "in_{$this->_entity}",
            'type'              => 'checkbox',
            'values'            => $this->_getSelectedValues(),
            'align'             => 'center',
            'use_index'         => true,
            'header_css_class'  => 'a-center',
        ));

        $this->addColumn("{$this->_entity}_id", array(
            'index'     => "{$this->_entity}_id",
            'header'    => Mage::helper('hd_bccp')->__('Id'),
            'sortable'  => true,
            'width'     => '60px',
        ));

        $this->addColumn('name', array(
            'index'     => 'name',
            'header'    => Mage::helper('hd_bccp')->__('Name'),
            'align'     =>'left',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/chooser', array(
            '_current'          => true,
            'current_grid_id'   => $this->getId(),
            'collapse'          => null
        ));
    }
    
    protected function _getSelectedValues()
    {
        $products = $this->getRequest()->getPost('selected', array());
        return $products;
    }
    
    
}