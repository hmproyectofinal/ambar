<?php

class Hd_Bccp_Block_Adminhtml_Bccp_Promo_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('promoGrid')
            ->setDefaultSort('name')
            ->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('hd_bccp/promo_collection')
            ->addPreparedData()
            ->addStoreIdsToResult();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $_helper = $this->_helper();

        
        $this->addColumn('promo_id', array(
            'index'     => 'promo_id',
            'header'    => $this->__('Promo ID'),
            'type'      => 'number',
            'align'     => 'center',
            'width'     => '10px',
        ));
        
        $this->addColumn('is_active', array(
            'index'     => 'is_active',
            'header'    => $this->__('Status'),
            'type'      => 'options',
            'align'     => 'center',
            'width'     => '50px',
            'options'    => array(
                '1' => $this->__('Active'),
                '0' => $this->__('Inactive'),
            ),
        ));
        
        $this->addColumn('name', array(
            'index'     => 'name',
            'header'    => $this->__('Promo Name'),
            'type'      => 'text',
            'align'     => 'left',
            'width'     => '400px',
        ));
        
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
        
        $bankOptions = Mage::getSingleton('hd_bccp/system_config_source_bank')->toOptionHash();
        $this->addColumn('bank_id', array(
            'index'     => 'bank_id',
            'header'    => $this->__('Bank'),
            'type'      => 'options',
            'align'     => 'center',
            'width'     => '160px',
            'options'   => $bankOptions,
        ));
        
        $ccOptions = Mage::getSingleton('hd_bccp/system_config_source_creditcard')->toOptionHash();
        $this->addColumn('creditcard_id', array(
            'index'     => 'creditcard_id',
            'header'    => $this->__('Credit Card'),
            'type'      => 'options',
            'align'     => 'center',
            'width'     => '160px',
            'options'   => $ccOptions,
        ));
        
        $this->addColumn('active_from_date', array(
            'index'     => 'active_from_date',
            'header'    => $this->__('Active From'),
            'type'      => 'date',
            'width'     => '80px',
            'align'     => 'center',
        ));
        
        $this->addColumn('active_to_date', array(
            'index'     => 'active_to_date',
            'header'    => $this->__('Active To'),
            'type'      => 'date',
            'width'     => '100px',
            'align'     => 'center',
        ));
        
        $this->addColumn('time_slot', array(
            'index'     => 'time_slot',
            'header'    => $this->__('Active Hours'),
            'type'      => 'text',
            'width'     => '120px',
            'align'     => 'center',
            'sortable'  => false,
            'filter'    => false,
        ));
        
        return parent::_prepareColumns();
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
