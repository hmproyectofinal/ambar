<?php

class WinWin_OpsIntegration_Block_Adminhtml_Execution_History extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Set template
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('winwin/opsintegration/execution/history.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock('winwin_opsintegration/adminhtml_execution_history_grid', 'admin.winwin.opsintegration.execution.history.grid'));
        return parent::_prepareLayout();
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
    
    public function getButtonsHtml($area = null)
    {        
        
        
        $b2 = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
			array(
	            'label'     => Mage::helper('winwin_opsintegration')->__('Import Prices'),
	            'onclick'   => 'setLocation(\''.$this->getUrl('*/*/priceimport').'\')',
	            //'class'     => 'add',
			)        
        )->toHtml();

        $b3 = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
			array(
	            'label'     => Mage::helper('winwin_opsintegration')->__('Import Stock'),
	            'onclick'   => 'setLocation(\''.$this->getUrl('*/*/stockimport').'\')',
	            //'class'     => 'add',
			)        
        )->toHtml();

        $b4 = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
            array(
                'label'     => Mage::helper('winwin_opsintegration')->__('Import Shipments'),
                'onclick'   => 'setLocation(\''.$this->getUrl('*/*/shipmentimport').'\')',
                //'class'     => 'add',
            )        
        )->toHtml();


        $b5 = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
            array(
                'label'     => Mage::helper('winwin_opsintegration')->__('Export Orders'),
                'onclick'   => 'setLocation(\''.$this->getUrl('*/*/orderexport').'\')',
                //'class'     => 'add',
            )        
        )->toHtml();

        $b6 = $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
            array(
                'label'     => Mage::helper('winwin_opsintegration')->__('Import Inc. Stock'),
                'onclick'   => 'setLocation(\''.$this->getUrl('*/*/stockimportinc').'\')',
                //'class'     => 'add',
            )        
        )->toHtml();
        
       
        return  $b2.$b3.$b6.$b4.$b5;
    }      

    public function isSingleStoreMode()
    {
        if (!Mage::app()->isSingleStoreMode()) {
               return false;
        }
        return true;
    }
}
