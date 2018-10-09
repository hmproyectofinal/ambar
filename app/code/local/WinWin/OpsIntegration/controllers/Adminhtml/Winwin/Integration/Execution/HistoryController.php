<?php

class WinWin_OpsIntegration_Adminhtml_WinWin_Integration_Execution_HistoryController extends Mage_Adminhtml_Controller_Action {

    public function preDispatch() {
        parent::preDispatch();    
        Mage::helper('winwin_opsintegration/data')->setUserIsCron(false);
        return $this;
    }

    public function downloadfileAction() {
        $defaultFolder = Mage::helper('winwin_opsintegration/data')->getDirectoryLocationWebsiteCode($this->getRequest()->getParam('website_code'));
        $path = Mage::getBaseDir('base') . DS . $defaultFolder . DS . 'Logs' . DS . $this->getRequest()->getParam('log_type') . DS;
        $file = $path . $this->getRequest()->getParam('integration_name') . ".log";
        header('Content-Description: File Transfer');
        header('Content-disposition: attachment; filename=' . $this->getRequest()->getParam('integration_name') . '.log');
        header('Content-Type: text/log');
        header('Content-Length: ' . filesize($file));
        $fp = fopen($file, 'rb');
        fpassthru($fp);
        exit;
    }

    public function orderexportAction() {
        Mage::getModel('winwin_opsintegration/Orderexporterp')
                ->getCsvFileToErp();
        Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('winwin_opsintegration')->__('ERP executed.'));
        $this->_redirect('*/*/');
        return;
    }

    public function priceimportAction() {
        Mage::getModel('winwin_opsintegration/Priceimporterp')
                ->getCsvPriceFileToMagento();

        Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('winwin_opsintegration')->__('ERP executed.'));
        $this->_redirect('*/*/');
        return;
    }

    public function stockimportAction() {
        Mage::getModel('winwin_opsintegration/Stockimporterp')
                ->getCsvStockFileToMagento();

        Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('winwin_opsintegration')->__('ERP executed.'));
        $this->_redirect('*/*/');
        return;
    }

    public function shipmentimportAction() {
        Mage::getModel('winwin_opsintegration/Shipmentimport')
                ->getCsvShipmentFileToMagento();

        Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('winwin_opsintegration')->__('Shipment executed.'));
        $this->_redirect('*/*/');
        return;
    }

    public function stockimportincAction() {

        Mage::getModel('winwin_opsintegration/Stockimportincerp')
                ->getCsvStockFileToMagento();

        Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('winwin_opsintegration')->__('ERP executed.'));
        $this->_redirect('*/*/');
        return;
    }

    public function stateimportAction() {
        Mage::getModel('winwin_opsintegration/Stateimporterp')
                ->getCsvStateFileToMagento();

        Mage::getSingleton('adminhtml/session')
                ->addSuccess(Mage::helper('winwin_opsintegration')->__('ERP executed.'));
        $this->_redirect('*/*/');
        return;
    }

    public function indexAction() {
        $this->_title($this->__('WinWin Integration'))
                ->_title($this->__('Manage Execution History'));

        $this->loadLayout();
        $this->renderLayout();
    }

    public function gridAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function infoAction() {
        $id = $this->getRequest()->getParam('id');

        if ($id != 0) {
            Mage::register('winwin_opsintegration_execution_history_info', 'ok');
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('winwin_opsintegration')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    public function massDeleteAction() {
        $selection = $this->getRequest()->getParam('winwin_opsintegration');

        foreach ($selection as $infoId) {
            $info = Mage::getModel('winwin_opsintegration/execution_history_info');
            $info->load($infoId);

            if ($info && $info->getId()) {
                try {
                    $info->delete();
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')
                            ->addError(Mage::helper('winwin_opsintegration')->__('Cannot delete item with id %s.', $info->getId()));
                    $this->_redirect('*/*/');
                }
            }
        }

        $this->_redirect('*/*/');
    }

}
