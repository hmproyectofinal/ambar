<?php

class WinWin_OpsIntegration_Model_Shipmentimport {

    protected $_file_extension = 'csv';

	public function getCsvShipmentFileToMagento() {

        $start_time = time();
		$helper = Mage::helper('winwin_opsintegration/data');

        $website_code = 'base'; //VER   

        

        $defaultFolder        = $helper->getDirectoryLocation($website_code);
        $delimiter            = $helper->getDelimiter($website_code);
        $enclosure            = $helper->getEnclosure($website_code);    

        $sendNotification     = $helper->getSendNotification($website_code);
        $generateInvoice      = $helper->getGenerateInvoice($website_code);
        $statusesAllowed      = $helper->getStatusesAllowed($website_code);    
        //$statusAfterImport    = $helper->getStatusAfterImport($website_code);  
        $messageTemplate      = $helper->getMessageTemplate ($website_code); 
        $chekFTP              = $helper->getCheckFTP($website_code);        
        $ftp_inbound          = $helper->getFTPInboundDirectory($website_code); 
    

        $debug_mode           = $helper->getDebugMode($website_code); 

        $csv_file_name        = $this->_getCSVFileName();
        $csv_file_name_prefix = $this->_getCSVFileNamePrefix();
        $csv_columns_count    = $this->_getCSVColumnsCount();



        $path = Mage::getBaseDir('base') . DS . $defaultFolder . DS . 'Inbound' . DS . 'Pending' . DS;
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true)->open(array('path' => $path));
                
        if ( ! $fileExists = $io->fileExists($csv_file_name) ){
            if (  $chekFTP ){

                $ftp_host = $helper->getFTPHost($website_code);
                $ftp_user = $helper->getFTPUser($website_code);
                $ftp_pass = $helper->getFTPPassword($website_code);
                $connect_string = $helper->getFTPConnectioString($ftp_host, $ftp_user, $ftp_pass);

                try{
                    $ftp_handler = Mage::getModel('winwin_opsintegration/lib_ftp');                
                    $ftp_handler->connect($connect_string);
                    $fileExists = $ftp_handler->download($ftp_inbound . DS . $csv_file_name, $path . $csv_file_name);                    
                } catch (Exception $e) {                    
                    Mage::log('ERROR DOWNLOADING FTP: ' . $e->getMessage(), 6, 'int.log');
                }    
                if ($fileExists){
                    $file_last_modified = date("YmdGis", $ftp_handler->mdtm($csv_file_name));                
                }        
                $deleleFileFTP  = $helper->getFTPDeleteFile($website_code);
                if ( $fileExists && $deleleFileFTP ){
                    try{
                        $ftp_handler->delete($ftp_inbound . DS . $csv_file_name);
                    } catch (Exception $e){
                        Mage::log('ERROR DELETING FILE FROM FTP: ' . $e->getMessage(), 6, 'int.log');
                    }
                }
                @$ftp_handler->close();
            }
        }else{
            $file_last_modified = date("YmdGis", filemtime($path . $csv_file_name));
        }   


        if (  $fileExists ){
        	$io->open(array('path' => $path));
            $moveFileToPath = Mage::getBaseDir('base') . DS . $defaultFolder . DS . 'Inbound' . DS . 'Processed' . DS;
			

			$_executedTimestamp = gmdate('YmdHis');
            $_executedTimestampDb = gmdate("Y-m-d H:i:s");
            
            

            $io->streamOpen($csv_file_name, 'r');
            
            $_errorLogsArr = array();
            $_recordProcessedCorrectly = 0;
            $_totalRecords = -1;

            

            while ($row = $io->streamReadCsv($delimiter, $enclosure)) {
                if ($_totalRecords++ == -1) continue;

                if ($debug_mode) Mage::log('LINE: ' . $_totalRecords, Zend_Log::DEBUG, 'int_debug.log');                    

                if (!(is_array($row) && count($row) == $csv_columns_count)) {
                    $log = 'Linea ' . $_totalRecords . ' - ' . implode($delimiter, $row) . ' Invalid format of the line. There should be ' . $csv_columns_count . ' columns/values in CSV file.';
                    $_errorLogsArr[] = $log;
                    if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                    
                    continue;
                }

                
                foreach ($row as $key => $value) {
                        $row[$key] = trim($value);
                }
                //Check if increment_id is a number
                $_incrementId = $row[0];
                if (!preg_match('/^[0-9]+$/', $_incrementId)) {
                    $log = 'Line ' . $_totalRecords . ' - ' . $_incrementId . ' IncrementID: The incrementIs is not valid non negative integer: ' . $_incrementId . '.';
                    $_errorLogsArr[] = $log;
                    if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                    
                    continue;
                }
                
                $_incrementId = (int) $_incrementId;

                //Check if increment_id exists
                $order = Mage::getModel('sales/order')->loadByIncrementId($_incrementId);

                if ( ! $order->getIncrementId() ) {
                    $log =  'Line ' . $_totalRecords . ' - ' . $_incrementId . ' IncrementID: The incrementId does not exist.';
                    $_errorLogsArr[] = $log;
                    if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                    
                    continue;
                }
                

                $_status = $order->getStatus();
                //var_dump($_status);exit;
                if (! in_array($_status,  $statusesAllowed)) {
                    $log = 'Line ' . $_totalRecords . ' - ' . $_incrementId . ' State: The order is "' . $_status . '" and cannot be shipped. See configuration.';
                    $_errorLogsArr[] = $log;
                    if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                    
                    continue;
                }


                $_save_order = false;
                if ($generateInvoice && ! $order->hasInvoices()) {
                    if(!$order->canInvoice()) {
                        $log = 'Line ' . $_totalRecords . ' - ' . $_incrementId . '  The order can not be invoiced.';                        
                        if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                    
                        
                    }else{
                        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                        $invoice->register();
         
                        $invoice->getOrder()->setCustomerNoteNotify(false);          
                        $invoice->getOrder()->setIsInProcess(true);
                        
         
                        $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($order);
         
                        $transactionSave->save();
                        $_save_order = true;
                        $log = 'Line ' . $_totalRecords . ' - ' . $_incrementId . '  Order Invoiced.';                        
                        if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                    
                    }

                }

                if ($order->hasShipments()){
                    $log = 'Line ' . $_totalRecords . ' - ' . $_incrementId . '  The order is already shipped';                    
                    if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                                        
                }else{

                    //si se puede mandar
                    if ($order->canShip()) {
                        try {
                            $shipment = Mage::getModel('sales/service_order', $order)
                                    ->prepareShipment();


                            $messageTracking = str_replace('{tracking_number}', $row[1], $messageTemplate);
                            
                            $trackingData = array(
                                'carrier_code' => 'custom',//$order->getShippingCarrier()->getCarrierCode(),
                                'title' => $order->getShippingCarrier()->getConfigData('title'),
                                'number' => $messageTracking,
                            );

                            $track = Mage::getModel('sales/order_shipment_track')
                                    ->addData($trackingData);


                            $shipment->addTrack($track);
                            // Register Shipment
                            $shipment->register();
                            // Save the Shipment
                            $shipment->getOrder()->setIsInProcess(true);
                            
                            $transaction = Mage::getModel('core/resource_transaction')
                                    ->addObject($shipment)
                                    ->addObject($order);
                            $transaction->save();

                            $log = 'Line ' . $_totalRecords . ' - ' . $_incrementId . '  Order Shipped';
                            if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                    

                            //envia el mail
                            $emailSentStatus = $shipment->getData('email_sent');
                            $customerEmail = $order->getCustomerEmail();
                            if (!is_null($customerEmail) && !$emailSentStatus && $sendNotification) {
                                $shipment->sendEmail(true, $customerEmail);
                                $shipment->setEmailSent(true);
                                $log = 'Line ' . $_totalRecords . ' - ' . $_incrementId . '  email sent';
                                if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                    
                            }
                                            


                            $order->setData('state', Mage_Sales_Model_Order::STATE_COMPLETE);
                            $order->setData('status', Mage_Sales_Model_Order::STATE_COMPLETE);
                            $_save_order = true;
                            
                        } catch (Exception $e) {
                            $log =  $_incrementId . '. Unable to save item. PHP Exception: ' . $e->getMessage();
                            $_errorLogsArr[] = $log;
                            if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                    
                            //die('fallo');
                            //continue;
                        }
                    } else {
                        $log = 'Line ' . $_totalRecords . ' - ' . $_incrementId . '  The order can not be shipping.';
                        $_errorLogsArr[] = $log;
                        if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                                        
                    }
                }

                if ($_save_order){
                    try {
                        $order->save();
                        $_recordProcessedCorrectly++;
                        if ($debug_mode) Mage::log('INCREMENT ID: ' . $_incrementId . ' save OK', Zend_Log::DEBUG, 'int_debug.log');
                    } catch (Exception $e) {
                        $_errorLogsArr[] = $_incrementId . '. Unable to save item. PHP Exception: ' . $e->getMessage();
                    }
                }
            }

            $fileMoved = 'OK';
            try {
                $io->checkAndCreateFolder($moveFileToPath);
                $io->mv($path . $csv_file_name, $moveFileToPath . $csv_file_name_prefix  . $file_last_modified . '_' . date('YmdGis') . '.' . $this->_file_extension);
                $io->streamClose();
            } catch (Exception $e) {
                $fileMoved = 'ERROR';
                $_errorLogsArr[] = 'File moved ERROR, ' . $csv_file_name . ', PHP Exception: ' . $e->getMessage();
                Mage::log($e->getMessage(), 6, 'int.log');
            }
          
            $_executionStatus = (count($_errorLogsArr) === 0) ? 'successful' : 'error';
            Mage::dispatchEvent(
                    'winwin_opsintegration_integration_execution', array(
                'rf04' =>
                array(
                    'integration_name' => 'Shipments_Import', /* Precios_Import / Stocks_Import / Ordenes_Export */
                    'executed_at' => $_executedTimestampDb,
                    'processed_file_name' => $csv_file_name,
                    'records_processed_correctly' => $_recordProcessedCorrectly,
                    'total_records' => $_totalRecords,
                    'execution_type' => Mage::helper('winwin_opsintegration/data')->_winwinUserIs, /* manual / automatic) */
                    'username' => Mage::helper('winwin_opsintegration/data')->getUser(), /* only if was executed manually */
                    'execution_status' => $_executionStatus, /* 'successful' or 'error' */
                ),
                'rf03' => $_errorLogsArr,
                //'store_id' => $store_id,
                'website_code' => $website_code,
                'file' => $path . $csv_file_name,
                'file_name' => $csv_file_name,
                'executed_timestamp' => $_executedTimestamp,
                'cost_time' => gmdate("H:i:s", time() - $start_time),
                'log_filename' => 'integracion_shipments.log', /* 'integracion_stock or integracion_precios or integracion_ordenes ' */
                'log_error' => 'errores_shipments.log', /* errores_stock  or errores_precios.log or errores_ordenes  */
                'file_moved' => $fileMoved,
                    )
            );
		}
	}


	protected function _getCSVFileName()
    {
        return 'shipments.csv';
    }

    protected function _getCSVFileNamePrefix()
    {
        return 'shipments_';
    }

    protected function _getCSVColumnsCount(){
    	return 2;
    }


}
