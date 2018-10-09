<?php

class Hd_Bccp_Block_Adminhtml_Bccp_Creditcard_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('creditcardGrid')
            ->setDefaultSort('name')
            ->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('hd_bccp/creditcard_collection')
            ->addStoreIdsToResult();
        $this->setCollection($collection);
        return parent::_prepareCollection();
        
        
    }

    protected function _prepareColumns()
    {
         $_helper = Mage::helper('hd_bccp');

        $this->addColumn('creditcard_id', array(
            'index'     => 'creditcard_id',
            'header'    => $this->__('Credit Card ID'),
            'type'      => 'number',
            'align'     => 'center',
            'width'     => '50px',
        ));
        
        $this->addColumn('name', array(
            'index'     => 'name',
            'header'    => $this->__('Credit Card Name'),
            'type'      => 'text',
            'align'     => 'left',
            'width'     => '300px',
        ));
        
        $this->addColumn('description', array(
            'index'     => 'description',
            'header'    => $this->__('Credit Card Description'),
            'type'      => 'text',
            'align'     => 'left',
            'width'     => '300px',
        ));
        
        // Add Method Columns        
        // Country If Available
//        if($this->_helper()->isCountrySupportEnable()) {
//            $countries = Mage::getSingleton('hd_bccp/system_config_source_country')->toOptionHash(false);
//            $this->addColumn('country_ids', array(
//                'index'     => 'country_ids',
//                'header'    => $this->__('Country'),
//                'type'      => 'options',
//                'options'   => $countries,
//            ));
//        }

        // Store If Available
        if($this->_helper()->isStoreSupportEnable()) {
            $stores = Mage::getSingleton('hd_bccp/system_config_source_store')->toOptionHash();
            $this->addColumn('store_ids', array(
                'index'     => 'store_ids',
                'header'    => $this->__('Stores'),
                'type'      => 'options',
                'sortable'  => false,
                'options'   => $stores,
                'filter_condition_callback' => array(
                    Mage::helper('hd_bccp/grid'), 'addGridStoreFilter'
                )
            ));
        }
        
        
    }
    
    public function getRowUrl($item)
    {
        return $this->getUrl('*/*/edit', array('id' => $item->getId()));
    }
    
    /**
     * @param string $key
     * @return Hd_Bccp_Helper_Data
     */
    protected function _helper($key = null)
    {
        return ($key) ? Mage::helper("hd_bccp/$key")
            : Mage::helper("hd_bccp");
    }

}
