<?php

class WinWin_OpsIntegration_Model_Stockimporterp {
    
    protected $_file_extension = 'csv';
    protected $_files_to_process = array();
    protected $_helper;
    protected $_website_code = 'admin';

    public function getCsvStockFileToMagento($_files_to_process = false) {
        $website_code  = $this->_website_code;
        $this->_helper = Mage::helper('winwin_opsintegration/data');

        if ($this->_helper->_winwinUserIs == 'automatic' && $this->_helper->getStockDisableCron($website_code)) return true;

        $start_time = time();
        
        $stock_field_index    = 2;
        $csv_columns_count    = 3;
        $file_processed       = false;   

        $this->_getFilesToProcess($_files_to_process); 
        


        $defaultFolder             = $this->_helper->getDirectoryLocation($website_code);
        $delimiter                 = $this->_helper->getDelimiter($website_code);
        $enclosure                 = $this->_helper->getEnclosure($website_code);
        $checkFTP                  = $this->_helper->getCheckFTP($website_code);                
        $states_reserved           = $this->_helper->getStatesReserved($website_code);    
        $updateStockAvailability   = $this->_helper->getUpdateStockAvailability($website_code);
        $updateStockAvailabilityBO = $this->_helper->getUpdateStockAvailabilityBO($website_code);
        $ftp_inbound               = $this->_helper->getFTPInboundDirectory($website_code);
	$flush_cache               = $this->_helper->getFlushCache($website_code);
        $debug_mode                = $this->_helper->getDebugMode($website_code); 
        
        

        $path = Mage::getBaseDir('base') . DS . $defaultFolder . DS . 'Inbound' . DS . 'Pending' . DS;
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true)->open(array('path' => $path));
        $io->open(array('path' => $path));
        $moveFileToPath = Mage::getBaseDir('base') . DS . $defaultFolder . DS . 'Inbound' . DS . 'Processed' . DS;


        foreach ($this->_files_to_process as $csv_file_name) {                 

            $csv_file_name_prefix = $csv_file_name . '-';
            $csv_file_name        = $csv_file_name . '.' .$this->_file_extension;

            if ($debug_mode) Mage::log('PROCESSING FILENAME: ' . $csv_file_name , Zend_Log::DEBUG, 'int_debug.log');

            if ( ! $fileExists = $io->fileExists($csv_file_name) ){
                if (  $checkFTP ){

                    $ftp_host = $this->_helper->getFTPHost($website_code);
                    $ftp_user = $this->_helper->getFTPUser($website_code);
                    $ftp_pass = $this->_helper->getFTPPassword($website_code);
                    $connect_string = $this->_helper->getFTPConnectioString($ftp_host, $ftp_user, $ftp_pass);

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

                    $deleleFileFTP  = $this->_helper->getFTPDeleteFile($website_code);
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
                    
            if ( $fileExists ){    
                $file_processed = true;        
                
                $reservedProducts = $this->getReservedProducts($states_reserved);  


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

                    //Check if QTY is valid non negative integer
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
                                    
                    if (is_array($reservedProducts)) {
                        //Check pending orders and discount stock
                        foreach ($reservedProducts as $order) {
                            if (is_array($order)) {
                                foreach ($order as $product) {
                                    if ($product['id'] == $_prodId) {
                                        $_qty_int -= (int) $product['qty'];
                                    }
                                }
                            }
                        }
                    }

                    //validaciones
                    if ($stockItem->getQty() == $_qty_int) {
                        $_recordProcessedCorrectly++;
                        if ($debug_mode) Mage::log('Product ID: ' . $_prodId . ' same stock.', Zend_Log::DEBUG, 'int_debug.log');                    
                        continue;
                    }

                    if ( ( $updateStockAvailability && $updateStockAvailabilityBO ) ||
                    ( $updateStockAvailability && (int)$stockItem->getBackorders() == 0 ) )                
                        
                    {                        
                        if ($_qty_int > 0) {
                            $stockItem->setIsInStock(1);
                        } else {
                            $stockItem->setIsInStock(0);
                        }
                    }

                    $stockItem->setQty($_qty_int);

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
                        'integration_name' => 'Stocks_Import', /* Precios_Import / Stocks_Import / Ordenes_Export */
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
                    'log_filename' => 'integracion_stock.log', /* 'integracion_stock or integracion_precios or integracion_ordenes ' */
                    'log_error' => 'errores_stock.log', /* errores_stock  or errores_precios.log or errores_ordenes  */
                    'file_moved' => $fileMoved,
                        )
                );
            }

        }

        if ($file_processed){
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
        }
    }

    public function getReservedProducts($states_reserved) {

        
        $result = array();

        $orders = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('status', array('in' => explode(",", $states_reserved)));                

        foreach ($orders as $order) {

            $products = Mage::getModel('sales/order_item')->getCollection()
                    ->addFieldToFilter('order_id', array('eq' => $order->getId()))
                    ->addFieldToFilter('product_type', 'simple');

            foreach ($products as $product) {

                $productLoaded = Mage::getModel('catalog/product')->load($product->getProductId());
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productLoaded);

                $result[$order->getId()][$product->getProductId()]['qty'] = $product->getQtyOrdered();
                $result[$order->getId()][$product->getProductId()]['id'] = $product->getProductId();                
            }
        }

        return $result;
    }

    protected function _getFilesToProcess($_files_to_process){
        if ($_files_to_process) {
            $this->_files_to_process = explode(',', $_files_to_process);
        }else{
            $this->_files_to_process =  explode(',' , $this->_helper->getStockFilesName($this->_website_code));        
        }
    }

}
