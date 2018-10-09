<?php

class WinWin_OpsIntegration_Model_Stockimportincerp {

    
    protected $_file_extension = 'csv';

    public function getCsvStockFileToMagento() {
       
        $start_time = time();
        
        $helper = Mage::helper('winwin_opsintegration/data');

        $website_code = 'base'; //VER   

        $defaultFolder        = $helper->getDirectoryLocation($website_code);
        $delimiter            = $helper->getDelimiter($website_code);
        $enclosure            = $helper->getEnclosure($website_code);
        $chekFTP              = $helper->getCheckFTP($website_code);        
        
        
        
        $csv_columns_count    = $this->_getCSVColumnsCount();
        $stock_field_index    = $this->_getStockFieldIndex(); 
        

        $debug_mode           = $helper->getDebugMode($website_code); 
	$flush_cache          = $helper->getFlushCache($website_code);



        //me fijo si hay algun archivo localmente
        $path = Mage::getBaseDir('base') . DS . $defaultFolder . DS . 'Inbound' . DS . 'Pending' . DS;
        $fileExists = $this->_checkLocalFiles($path);

        
        //si no hay me fijo en el FTP
        if ( ! $fileExists ){
            if (  $chekFTP ){

                $ftp_host = $helper->getFTPHost($website_code);
                $ftp_user = $helper->getFTPUser($website_code);
                $ftp_pass = $helper->getFTPPassword($website_code);
                $ftp_inbound  = $helper->getFTPInboundDirectory($website_code);
                $connect_string = $helper->getFTPConnectioString($ftp_host, $ftp_user, $ftp_pass);

                
                $ftp_handler = Mage::getModel('winwin_opsintegration/lib_ftp');                                        
                $ftp_handler->connect($connect_string);
                $list = $ftp_handler->ls($ftp_inbound . DS);
                
                foreach ($list as $file) {
                    $_fullFileName = $this->_parseFileName($file['name']);
                    if (  !$file['dir'] &&  strtolower($_fullFileName[0]) === $this->_getCSVFileNamePrefix() ){
                        try{                    
                            $ftp_handler->download($ftp_inbound . DS . $file['name'], $path . $file['name']);
                            $fileExists = true;
                            $deleleFileFTP  = $helper->getFTPDeleteFile($website_code);
                            if ( $deleleFileFTP ){
                                try{
                                    $ftp_handler->delete($ftp_inbound . DS . $file['name']);
                                } catch (Exception $e){
                                    Mage::log('ERROR DELETING FILE FROM FTP: ' . $e->getMessage(), 6, 'int.log');
                                }
                            }            
                        } catch (Exception $e) {                    
                            Mage::log('ERROR DOWNLOADING FTP: ' . $e->getMessage(), 6, 'int.log');
                        }                        
                    }
                }                                                                                            
                @$ftp_handler->close();
            }
        }    
        
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true)->open(array('path' => $path));
        $list = $io->ls(Varien_Io_File::GREP_FILES);

        foreach ($list as $file) {
            $io->open(array('path' => $path));
            $moveFileToPath = Mage::getBaseDir('base') . DS . $defaultFolder . DS . 'Inbound' . DS . 'Processed' . DS;
            $csv_file_name = $file['text'];
            
                    
            $_fullFileName = explode('_', $csv_file_name);
            if (!(is_array($_fullFileName) && count($_fullFileName) === 2)) {
                continue;
            }
            
            $_actionCsv = strtolower($_fullFileName[0]);
                               
            if ($_actionCsv !== 'stockinc') {
                continue;
            }
            


            //START parsing
            $_executedTimestamp = gmdate('YmdHis');
            $_executedTimestampDb = gmdate("Y-m-d H:i:s");
            $io->streamOpen($csv_file_name, 'r');
            
            $_errorLogsArr = array();
            $_recordProcessedCorrectly = 0;
            $_totalRecords = -1;

            // obtiene TODOS los sku en un array
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $table = $resource->getTableName('catalog/product');

            //array con todos los skus
            $skus = $readConnection->fetchPairs('SELECT sku,entity_id FROM ' . $table . ' WHERE type_id = "simple"');
            $update_products = array();

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
                $_sku = $row[0];

                if (!isset($skus[$_sku])) {
                    $log = 'Linea ' . $_totalRecords . ' - ' . $_sku . ' SKU: The sku does not exist.';
                    //$_errorLogsArr[] = $log;
                    if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                    
                    continue;
                }
                
                //Check if QTY is valid 
                $_qty = $row[$stock_field_index];

                if (!preg_match('/^[-+]?\d+$/', $_qty)) {
                    $log = 'Line ' . $_totalRecords . ' - ' . $_sku . ' QTY: The qty is not valid integer: ' . $_qty . '.';
                    $_errorLogsArr[] = $log;
                    if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                                     
                    continue;
                }
                               
                
                $prodId = $skus[$_sku];
                
                //lo libero para ahorra memoria;
                unset($skus[$_sku]);

                $update_products[$prodId] = array(
                    'qty' => (int) $_qty,
                    'line' => $_totalRecords,
                    'sku' => $_sku);
               
            }

            unset($skus);

            $stock_up = array_keys($update_products);

            $stocks = Mage::getModel('cataloginventory/stock_item')->getCollection()
                    ->addFieldToFilter('product_id', array('in' => $stock_up));
            unset($stock_up);
            

            /// ACTUALIZACION DE PRODUCTOS
            foreach ($stocks as $stockItem) {

                $_prodId = $stockItem->getProductId();                
                $_qty_int = $update_products[$_prodId]['qty'];

                $current_qty = $stockItem->getQty();

                $new_qty = $current_qty + $_qty_int;

                $updateStockAvailability   = Mage::helper('winwin_opsintegration/data')->getUpdateStockAvailability($website_code);
                $updateStockAvailabilityBO = Mage::helper('winwin_opsintegration/data')->getUpdateStockAvailabilityBO($website_code);
                                
                if ( ( $updateStockAvailability && $updateStockAvailabilityBO ) ||
                   ( $updateStockAvailability && (int)$stockItem->getBackorders() == 0 ) ) 
                {               
                    if ($new_qty > 0) {
                        $stockItem->setIsInStock(1);
                    } else {
                        $stockItem->setIsInStock(0);
                    }
                }

                $stockItem->setQty($new_qty);

                try {
                    $stockItem->save();
                    $_recordProcessedCorrectly++;
                    if ($debug_mode) Mage::log('Product ID: ' . $_prodId . ' save OK', Zend_Log::DEBUG, 'int_debug.log');                    
                } catch (Exception $e) {
                    $log = 'Line ' . $update_products[$_prodId]['line'] . ' - ' . $update_products[$_sku]['line'] . '. Unable to save stock. PHP Exception: ' . $e->getMessage();
                    $_errorLogsArr[] = $log;
                    if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                                        
                }
            }
            

	    //'cataloginventory_stock',
            $process = Mage::getModel('index/process')->load(8);            
            $process->reindexAll();


            if ($flush_cache) {
                try{
                    Mage::app()->getCacheInstance()->flush();
                    Mage::app()->cleanCache();
                    if ($debug_mode) Mage::log('Cleaning cache OK', Zend_Log::DEBUG, 'int_debug.log');                                        
                } catch (Exception $e) {
                    $log = 'Error cleaning cache '. $e->getMessage(); 
                    if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                                        
                }
            }
            $fileMoved = 'OK';
            
            try {
                $io->checkAndCreateFolder($moveFileToPath);
                $_fullFileName = $this->_parseFileName($csv_file_name);
                $io->mv($path . $csv_file_name, $moveFileToPath . $this->_getCSVFileNamePrefix() . '_' . $_fullFileName[1] . '_' . date('YmdGis') . '.' . $this->_file_extension);
                $io->streamClose();
            } catch (Exception $e) {
                $fileMoved = 'ERROR';
                $_errorLogsArr[] = 'File moved ERROR, ' . $_file . ', PHP Exception: ' . $e->getMessage();
                Mage::log($e->getMessage(), 6, 'int.log');
            }
            $_executionStatus = (count($_errorLogsArr) === 0) ? 'successful' : 'error';
            
           
            Mage::dispatchEvent(
                    'winwin_opsintegration_integration_execution', array(
                'rf04' =>
                array(
                    'integration_name' => 'Stocksinc_Import', /* Precios_Import / Stocks_Import / Ordenes_Export */
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
                'log_filename' => 'integracion_stockinc.log', /* 'integracion_stock or integracion_precios or integracion_ordenes ' */
                'log_error' => 'errores_stockinc.log', /* errores_stock  or errores_precios.log or errores_ordenes  */
                'file_moved' => $fileMoved,
                    )
            );  
        }
        
    }

    protected function _checkLocalFiles($path){
        
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true)->open(array('path' => $path));
        $list = $io->ls(Varien_Io_File::GREP_FILES);

        foreach ($list as $file) {
            $fileName = $this->_parseFileName($file['text']);
            if ( strtolower($fileName[0]) === $this->_getCSVFileNamePrefix() ) return true;
        }

        return false;

    }

    protected function _parseFileName($csv_file_name){

        $_fullFileName = explode('_', $csv_file_name);
        if (!(is_array($_fullFileName) && count($_fullFileName) === 2)) {
            return false;
        }
        $fileParsed = array();
        $fileParsed[0] = $_fullFileName[0];
        $file_timestamp = explode('.', $_fullFileName[1]);
        $fileParsed[1] = $file_timestamp[0];            
        return $fileParsed;
            
    }

    protected function _getStockFieldIndex()
    {        
        return 2;
    }
    

    protected function _getCSVColumnsCount()
    {
        return 3;
    }

    protected function _getCSVFileNamePrefix()
    {
        return 'stockinc';
    }



}
