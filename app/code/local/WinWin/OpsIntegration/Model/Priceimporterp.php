<?php

class WinWin_OpsIntegration_Model_Priceimporterp {

    protected $_file_extension = 'csv';
    protected $_files_to_process = array();
    protected $_helper;
    protected $_website_code = 'admin';

    public function getCsvPriceFileToMagento($_files_to_process = false) {
        $website_code = $this->_website_code;
        $this->_helper = Mage::helper('winwin_opsintegration/data');


        if ($this->_helper->_winwinUserIs == 'automatic' && $this->_helper->getPriceDisableCron($website_code)) return true;

        $start_time = time();

        
        $price_field_index    = 2;
        $special_price_index  = 3;
        $csv_columns_count    = 4;    
        $file_processed       = false;

        
        $this->_getFilesToProcess($_files_to_process);


        $defaultFolder        = $this->_helper->getDirectoryLocation($website_code);
        $delimiter            = $this->_helper->getDelimiter($website_code);
        $enclosure            = $this->_helper->getEnclosure($website_code);
        $checkFTP             = $this->_helper->getCheckFTP($website_code);                        
        $ignore_special_price = $this->_helper->getIgnoreSpecialPrice($website_code);     
        $ftp_inbound          = $this->_helper->getFTPInboundDirectory($website_code);        
        

        $flush_cache   = $this->_helper->getFlushCache($website_code);
        $debug_mode    = $this->_helper->getDebugMode($website_code);                  
        $update_config_price  = $this->_helper->getUpdateConfigPrice($website_code); 

                    
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

                $skus = array_fill_keys($readConnection->fetchCol('SELECT sku FROM ' . $table . ' WHERE type_id = "simple"'), ''); 

                //array con todos los skus
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
                    //Check if SKU exists
                    $_sku = $row[0];

                    if (!isset($skus[$_sku])) {
                        $log = 'Linea ' . $_totalRecords . ' - ' . $_sku . ' SKU: The sku does not exist.';
                        //$_errorLogsArr[] = $log;
                        if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                    
                        continue;
                    }

                    //lo libero para ahorra memoria;
                    unset($skus[$_sku]);

                    $update_products[$_sku] = array('price' => null,
                        'status' => null,
                        'line' => $_totalRecords,
                        'special_price' => null,
                        'special_price_from' => null);

                    
                    $_price = $row[$price_field_index];
                    
                                
                    $_price = ltrim($_price, '0');
                    $_price += 0;

                    if ($_price <= 0) {
                        $log = 'Line ' . $_totalRecords . ' - ' . $_sku . ' PRICE/PRECIO_BASE is NOT valid non negative real number: ' . $_price . '.';
                        $_errorLogsArr[] = $log;
                        if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                    
                        unset($update_products[$_sku]);                    
                        continue;
                    }                
                    $update_products[$_sku]['price'] = $_price;            
                    $update_products[$_sku]['line'] = $_totalRecords;
                    

                    if ( ! $ignore_special_price ){
                        $_specialPrice = $row[$special_price_index];        
                        
                        if ( $_specialPrice ){                    
                            $_specialPrice = ltrim($_specialPrice, '0');
                            $_specialPrice += 0;
                            if ($_specialPrice <= 0) {
                                $log = 'Line ' . $_totalRecords . ' - ' . $_sku . ' SPECIAL PRICE/PRECIO_ESPECIAL is NOT valid non negative real number: ' . $_specialPrice . '.';                            
                                $_errorLogsArr[] = $log;
                                if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                    
                                unset($update_products[$_sku]);                    
                                continue;
                            }                
                            $specialDateFrom = date("Y-m-d");                    
                            $update_products[$_sku]['special_price'] = $_specialPrice;
                            $update_products[$_sku]['special_price_from'] = $specialDateFrom;                    
                        }else{
                            $update_products[$_sku]['special_price'] = null;
                            $update_products[$_sku]['special_price_from'] = null;
                        }
                    }
                                                
                } //fin de while

                unset($skus);                

                /*Fix para forzar que la consulta no la haga sobre las tablas flat*/
                Mage::app()->getStore()->setConfig(Mage_Catalog_Helper_Product_Flat::XML_PATH_USE_PRODUCT_FLAT, '0');

                
                                
                $skus_up = array_keys($update_products);
                $prods = Mage::getModel('catalog/product')->getCollection()                            
                        ->addAttributeToSelect('sku')
                        ->addAttributeToSelect('price')                    
                        ->addAttributeToSelect('special_price')                    
                        ->addFieldToFilter('sku', array('in' => $skus_up));

                unset($skus_up);
                            

                /// ACTUALIZACION DE PRODUCTOS
                foreach ($prods as $product) {                    
                    $_sku = $product->getSku();
                    
                    //validaciones
                    $_price = $update_products[$_sku]['price'];                
                    if ( ! $ignore_special_price ){
                        $_specialPrice = $update_products[$_sku]['special_price'];
                        $_specialDateFrom = $update_products[$_sku]['special_price_from'];
                

                        if ($product->getPrice() == $_price && $product->getSpecialPrice() == $_specialPrice ) {
                            $_recordProcessedCorrectly++;
                            if ($debug_mode) Mage::log('SKU: ' . $_sku . ' same price and special price.', Zend_Log::DEBUG, 'int_debug.log');                    
                            continue;
                        }
                    }else{
                        if ($product->getPrice() == $_price ) {
                            $_recordProcessedCorrectly++;
                            if ($debug_mode) Mage::log('SKU: ' . $_sku . ' same price.', Zend_Log::DEBUG, 'int_debug.log');                    
                            continue;
                        }    
                    }
                                                
                    try {
                    
                        $product->setPrice($_price);
                        $product->getResource()->saveAttribute($product, 'price');                            
                        if ( ! $ignore_special_price){
                            $product->setSpecialPrice($_specialPrice);
                            $product->getResource()->saveAttribute($product, 'special_price');                            
                            $product->setSpecialFromDate($_specialDateFrom);
                            $product->getResource()->saveAttribute($product, 'special_from_date');
                            $product->setSpecialToDate(false);
                            $product->getResource()->saveAttribute($product, 'special_to_date');
                        }                            
                        $_recordProcessedCorrectly++;
                        if ($debug_mode) Mage::log('SKU: ' . $_sku . ' save OK', Zend_Log::DEBUG, 'int_debug.log');                    
                    } catch (Exception $e) {
                        $log = 'Line ' . $update_products[$_sku]['line'] . ' - ' . $_sku . '. Unable to save item. PHP Exception: ' . $e->getMessage();
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
                    Mage::log('CANT MOVE FILE ' . $e->getMessage(), 6, 'int.log');
                }            

                $_executionStatus = (count($_errorLogsArr) === 0) ? 'successful' : 'error';
                Mage::dispatchEvent(
                        'winwin_opsintegration_integration_execution', array(
                    'rf04' =>
                    array(
                        'integration_name' => 'Precios_Import', /* Precios_Import / Stocks_Import / Ordenes_Export */
                        'executed_at' => $_executedTimestampDb,
                        'processed_file_name' => $csv_file_name,
                        'records_processed_correctly' => $_recordProcessedCorrectly,
                        'total_records' => $_totalRecords,
                        'execution_type' => $this->_helper->_winwinUserIs, /* manual / automatic) */
                        'username' => $this->_helper->getUser(), /* only if was executed manually */
                        'execution_status' => $_executionStatus, /* 'successful' or 'error' */
                    ),
                    'rf03' => $_errorLogsArr,
                    //'store_id' => null,//$store_id,
                    'website_code' => $website_code,
                    'file' => $path . $csv_file_name,
                    'file_name' => $csv_file_name,
                    'executed_timestamp' => $_executedTimestamp,
                    'cost_time' => gmdate("H:i:s", (time() - $start_time)),
                    'log_filename' => 'integracion_precios.log', /* 'integracion_stock or integracion_precios or integracion_ordenes ' */
                    'log_error' => 'errores_precios.log', /* errores_stock  or errores_precios.log or errores_ordenes  */
                    'file_moved' => $fileMoved,
                        )
                );    
            }
        }

        if ($file_processed){
            //Actualizacion de precios configurables
            if ($update_config_price){              
                if ($debug_mode) Mage::log('UPDATING CONFIGURABLE PRICES', Zend_Log::DEBUG, 'int_debug.log');
                $table = $resource->getTableName('catalog/product');

                $configurables = $readConnection->fetchPairs('SELECT entity_id,sku FROM ' . $table . ' WHERE type_id = "configurable"' );

                $update_products_configurable = array();
                                            
                if (count($configurables) > 0) {
             
                    foreach ($configurables as $configurable_id => $sku) {                                

                        $product = Mage::getModel('catalog/product')->load($configurable_id);
                        $configurable= Mage::getModel('catalog/product_type_configurable')->setProduct($product);
                                    
                        $children =  $configurable->getUsedProductCollection()
                                ->addAttributeToSelect('sku')                    
                                ->addAttributeToSelect('price')                    
                                ->addAttributeToSelect('special_price')                    
                                ->addAttributeToSelect('is_in_stock')
                                ->addAttributeToSelect('special_to_date')                    
                                ->addAttributeToSelect('special_from_date')
                                ->setOrder('price','ASC');
                        

                        if (count($children) > 0){
                                    
                            $childPriceLowest           = 0;
                            $childSpecialPriceLowest    = 0;            
                            $first_simple = true;

                            
                            foreach($children as $_child ){    
                                                                
                                
                                //Tomo el precio del primer simple (el mas bajo porque esta ordenado). 
                                //Si no hay stock en ningun simple voy a usar este precio.
                                if ($first_simple){
                                    $first_simple = false;


                                    if($_child->getPrice() > $_child->getSpecialPrice()){
                                        $update_products_configurable[$configurable_id] = array(
                                                    'price'             => $_child->getPrice(),
                                                    'special_date_from' => $_child->getSpecialFromDate(),
                                                    'special_date_to'   => $_child->getSpecialToDate(),
                                                    'special_price'     => $_child->getSpecialPrice()
                                                    );
                                    }else{
                                        $update_products_configurable[$configurable_id] = array(
                                                    'price'             => $_child->getPrice(),
                                                    'special_date_from' => false,
                                                    'special_date_to'   => false,
                                                    'special_price'     => false
                                                    );
                                    }                              
                                }
                                                                
                                
                                if ( ! $_child->getIsInStock() )          continue;
                                
                                if($childPriceLowest == 0 || $childPriceLowest > $_child->getPrice() ){
                                        $childPriceLowest =  $_child->getPrice();
                                }
                                if($_child->getPrice() > $_child->getSpecialPrice() && ( $childSpecialPriceLowest > $_child->getSpecialPrice() || $childSpecialPriceLowest == 0 ) ){
                                    $childSpecialFromDate    =  $_child->getSpecialFromDate();
                                    $childSpecialToDate      =  $_child->getSpecialToDate();
                                    $childSpecialPriceLowest =  $_child->getSpecialPrice();
                                }                                                                 
                            }       
                            
                            if ($childPriceLowest != 0){

                                if ($childSpecialPriceLowest != 0 && $childSpecialPriceLowest < $childPriceLowest){
                                        $update_products_configurable[$configurable_id] = array(
                                                    'price'             => $childPriceLowest,
                                                    'special_date_from' => $childSpecialFromDate,
                                                    'special_date_to'   => $childSpecialToDate,
                                                    'special_price'     => $childSpecialPriceLowest
                                                    );
                                }else{
                                        $update_products_configurable[$configurable_id] = array(
                                                    'price'             => $childPriceLowest,
                                                    'special_date_from' => false,
                                                    'special_date_to'   => false,
                                                    'special_price'     => false
                                                    );
                                }            
                            }
                        }
                        

                        unset($configurables[$configurable_id]);
                        unset($product);
                        unset($configurable);
                    }    
                }
                unset($children);
                if (count($update_products_configurable) > 0){    
                    $products = Mage::getModel('catalog/product')->getCollection()                    
                            ->addAttributeToSelect('price')                    
                            ->addAttributeToSelect('special_price')                    
                            ->addAttributeToSelect('special_to_date')                    
                            ->addAttributeToSelect('special_from_date')                    
                            ->addAttributeToSelect('id')                    
                            ->addFieldToFilter('entity_id', array('in' => array_keys($update_products_configurable)));  
                    
                    $i=1;

                    foreach ($products as $_product) {

                        
                        $id = $_product->getId();
                        if ($update_products_configurable[$id]['price'] == 0) continue;
                        $price         = $update_products_configurable[$id]['price'];
                        $special_price = $update_products_configurable[$id]['special_price'];
                        $special_from  = $update_products_configurable[$id]['special_date_from'];
                        $special_to    = $update_products_configurable[$id]['special_date_to'];

                        if ($_product->getPrice() == $price && $_product->getSpecialPrice() == $special_price ){
                            $log = "OK: "  . $id .  ' ' . $i;
                            if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                             
                            $i++;
                            continue;
                        } 

                        $_product->setPrice( $price );        
                        $_product->setSpecialPrice( $special_price );        
                        $_product->setSpecialFromDate( $special_from );        
                        $_product->setSpecialToDate( $special_to );
                                
                        try{
                    
                            $_product->getResource()->saveAttribute($_product, 'price');               
                            $_product->getResource()->saveAttribute($_product, 'special_price');        
                            $_product->getResource()->saveAttribute($_product, 'special_to_date');    
                            $_product->getResource()->saveAttribute($_product, 'special_from_date');    
                            $log = "OK: "  . $id .  ' ' . $i;
                            if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                             
                        }
                        catch (Exception $e) {            
                            $log = "ERROR: "  . $id .  ' ' . $i . '  ' . $e->getMessage();
                            if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                             
                        }
                        $i++;        
                    }    
                    
                }

            }

            if ($debug_mode) Mage::log('REINDEXING', Zend_Log::DEBUG, 'int_debug.log');
            //'catalogsearch_fulltext',
            // $process = Mage::getModel('index/process')->load(7);
            // $process->reindexAll();

            //'catalog_product_price',
            $process = Mage::getModel('index/process')->load(2);
            $process->reindexAll();

            //'catalog_category_product',
            $process = Mage::getModel('index/process')->load(6);
            $process->reindexAll();
            
            

            //'catalog_product_flat',
            $process = Mage::getModel('index/process')->load(5);
            $process->reindexAll();

            if ($flush_cache) {
                if ($debug_mode) Mage::log('FLUSHING CACHE', Zend_Log::DEBUG, 'int_debug.log');
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

    protected function _getFilesToProcess($_files_to_process){
        if ($_files_to_process) {
            $this->_files_to_process = explode(',', $_files_to_process);
        }else{
            $this->_files_to_process =  explode(',' , $this->_helper->getPriceFilesName($this->_website_code));        
        }
    }


}
