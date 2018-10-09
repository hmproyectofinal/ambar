<?php

class WinWin_OpsIntegration_Block_Adminhtml_Execution_History_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        
        $this->setId('historyGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');

        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);

    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('winwin_opsintegration/execution_history_info')->getCollection();
        
        $this->setCollection($collection);

        parent::_prepareCollection();
        
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('winwin_opsintegration')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'id',
        ));

        $this->addColumn('integration_name', array(
            'header'    => Mage::helper('winwin_opsintegration')->__('Integration'),
            'type'  => 'options',
            'options' => array(
                WinWin_OpsIntegration_Model_Execution_History_Info::EXECUTION_INTEGRATION_PRICES_IMPORT => Mage::helper('winwin_opsintegration')->__('Precios_Import'),
                WinWin_OpsIntegration_Model_Execution_History_Info::EXECUTION_INTEGRATION_STOCKS_IMPORT => Mage::helper('winwin_opsintegration')->__('Stocks_Import'),
                WinWin_OpsIntegration_Model_Execution_History_Info::EXECUTION_INTEGRATION_STOCKSINC_IMPORT => Mage::helper('winwin_opsintegration')->__('Stocksinc_Import'),
                WinWin_OpsIntegration_Model_Execution_History_Info::EXECUTION_INTEGRATION_ORDERS_EXPORT => Mage::helper('winwin_opsintegration')->__('Ordenes_Export'),
				WinWin_OpsIntegration_Model_Execution_History_Info::EXECUTION_INTEGRATION_STATES_IMPORT => Mage::helper('winwin_opsintegration')->__('Estados_import'),
                WinWin_OpsIntegration_Model_Execution_History_Info::EXECUTION_INTEGRATION_SHIPMENTS_IMPORT => Mage::helper('winwin_opsintegration')->__('Shipments_Import'),
            ),
            'index'     => 'integration_name',
        ));

        $this->addColumn('executed_at', array(
            'header'    => Mage::helper('winwin_opsintegration')->__('Executed at'),
            'type' => 'datetime',
            'index'     => 'executed_at',
        ));

        $this->addColumn('processed_file_name', array(
            'header'    => Mage::helper('winwin_opsintegration')->__('Processed file'),
            'index'     => 'processed_file_name',
        ));
        $this->addColumn('records_processed_correctly', array(
            'header'    => Mage::helper('winwin_opsintegration')->__('Records processed correctly'),
            'type'  => 'number',
            'index'     => 'records_processed_correctly',
        ));

        $this->addColumn('total_records', array(
            'header'    => Mage::helper('winwin_opsintegration')->__('Total records'),
            'type'  => 'number',
            'index'     => 'total_records',
        ));

        $this->addColumn('execution_type', array(
            'header'    => Mage::helper('winwin_opsintegration')->__('Execution type'),
            'index'     => 'execution_type',
            'type'  => 'options',
            'options' => array(
                WinWin_OpsIntegration_Model_Execution_History_Info::EXECUTION_TYPE_MANUAL => Mage::helper('winwin_opsintegration')->__('Manual'),
                WinWin_OpsIntegration_Model_Execution_History_Info::EXECUTION_TYPE_AUTOMATIC => Mage::helper('winwin_opsintegration')->__('Automatic'),
            )
    ));

        $this->addColumn('username', array(
            'header'    => Mage::helper('winwin_opsintegration')->__('Username'),
            'index'     => 'username',
        ));

        $this->addColumn('execution_status', array(
            'header'    => Mage::helper('winwin_opsintegration')->__('Execution status'),
            'index'     => 'execution_status',
            'type'  => 'options',
            'options' => array(
                WinWin_OpsIntegration_Model_Execution_History_Info::EXECUTION_STATUS_SUCCESSFUL => Mage::helper('winwin_opsintegration')->__('Successful'),
                WinWin_OpsIntegration_Model_Execution_History_Info::EXECUTION_STATUS_ERROR => Mage::helper('winwin_opsintegration')->__('Error'),
            )
        ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('winwin_opsintegration');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'    => Mage::helper('winwin_opsintegration')->__('Delete'),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => Mage::helper('winwin_opsintegration')->__('Are you sure?')
        ));       

       return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/info', array(
            'store'=>$this->getRequest()->getParam('store'),
            'id'=>$row->getId())
        );
    }
}
