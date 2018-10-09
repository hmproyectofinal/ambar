<?php

class WinWin_OpsIntegration_Block_Adminhtml_Execution_History_Info extends Mage_Adminhtml_Block_Widget_Container
{
    /**
     * Set template
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('winwin/opsintegration/execution/history/info.phtml');
    }

    protected function _prepareLayout()
    {
        $this->setChild('form', $this->getLayout()->createBlock('winwin_opsintegration/adminhtml_execution_history_info_form', 'admin.winwin.opsintegration.execution.history.info.form'));
        return parent::_prepareLayout();
    }

    public function getFormHtml()
    {
        return $this->getChildHtml('form');
    }
    
    public function getInfoObject()
    {
    	return Mage::getModel('winwin_opsintegration/execution_history_info')->load($this->getRequest()->getParam('id'));
    }
    
    /**
     *  $logType = Errors or Executions
     *  $integrationName integracion_stock or integracion_precios or integracion_ordenes or 
     *  errores_stock.log or errores_precios.log or errores_ordenes.log
     */
    public function getFileDownload($integrationName, $logType, $webiste_code)
    {
    	//$integrationName = orderExport or stockImport or priceImport
    	return $this->getUrl('*/*/downloadfile', array('integration_name' => $integrationName, 'log_type' => $logType, 'website_code' => $webiste_code));
    }    
  

    public function isSingleStoreMode()
    {
        if (!Mage::app()->isSingleStoreMode()) {
               return false;
        }
        return true;
    }
}
