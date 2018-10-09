<?php

class Hd_Bccp_Block_Adminhtml_Bccp_Bank_Grid 
    extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('bankGrid')
            ->setDefaultSort('name')
            ->setDefaultDir('ASC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('hd_bccp/bank_collection')
            ->addStoreIdsToResult();
        $this->setCollection($collection);
        parent::_prepareCollection();
        
        // Append "All Countries" Fake Value
        foreach($collection as $record) {
            if (!$record->getData('country_id')) {
                $record->setData('country_id', '*');
            }
        }
        
        return $this;
    }

    protected function _prepareColumns()
    {
        $_helper = Mage::helper('hd_bccp');

        $this->addColumn('bank_id', array(
            'index'     => 'bank_id',
            'header'    => $this->__('Bank ID'),
            'type'      => 'number',
            'align'     => 'center',
            'width'     => '50px',
        ));
        
        $this->addColumn('name', array(
            'index'     => 'name',
            'header'    => $this->__('Bank Name'),
            'type'      => 'text',
            'align'     => 'left',
            'width'     => '300px',
        ));
        
        $this->addColumn('description', array(
            'index'     => 'description',
            'header'    => $this->__('Bank Description'),
            'type'      => 'text',
            'align'     => 'left',
            'width'     => '300px',
        ));
        
        // Country If Available
        if($this->_helper()->isCountrySupportEnable()) {
            $countries = Mage::getSingleton('hd_bccp/system_config_source_country')
                ->toOptionHash(true, true);
            $this->addColumn('country_id', array(
                'index'     => 'country_id',
                'header'    => $this->__('Country'),
                'type'      => 'options',
                'options'   => $countries,
                'filter_condition_callback' => array(
                    Mage::helper('hd_bccp/grid'), 'addBankGridCountryFilter'
                )
            ));
        }
        
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
