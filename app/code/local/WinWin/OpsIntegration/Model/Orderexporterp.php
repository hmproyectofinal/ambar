<?php

class WinWin_OpsIntegration_Model_Orderexporterp {

    protected $_exportPageSize;
    protected $_exportCollectionLimit;
    protected $_skipIncrementId = array();
    
    protected function cleanStreet($street){        
        $tmp = '';
        for ($i = 0; $i < strlen($street); $i++) {
            $tmp.=((($street[$i] >= '0') and ($street[$i] <= '9')) || (($street[$i] >= 'A') and ($street[$i] <= 'z')) ) ? $street[$i] : ' ';
        }
        return $tmp;
    }

    protected function getCollection($fromStatuses, $dni_in_shipping = false) {

        $shipping_address_fields = array(
                    'shipping_address.lastname AS shipping_lastname',
                    'shipping_address.firstname AS shipping_firstname',
                    'shipping_address.street AS shipping_street',
                    'shipping_address.postcode AS shipping_postcode',
                    'shipping_address.city AS shipping_city',
                    'shipping_address.region AS shipping_region',
                    'shipping_address.region_id AS shipping_region_id',
                    'shipping_address.country_id AS shipping_country_id',
                    'shipping_address.telephone AS shipping_telephone',
                    'shipping_address.fax AS shipping_fax',
                    'shipping_address.email AS customer_email');

        if ($dni_in_shipping){
            $shipping_address_fields[] = 'shipping_address.dni AS customer_dni';
        }                    

        $billing_address_fiels = array(
                    'billing_address.lastname AS billing_lastname',
                    'billing_address.firstname AS billing_firstname',
                    'billing_address.street AS billing_street',
                    'billing_address.postcode AS billing_postcode',
                    'billing_address.city AS billing_city',
                    'billing_address.region AS billing_region',
                    'billing_address.region_id AS billing_region_id',
                    'billing_address.country_id AS billing_country_id',
                    'billing_address.telephone AS billing_telephone',
                    'billing_address.fax AS billing_fax');


        $orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('entity_id')
                ->addAttributeToSelect('status')
                ->addAttributeToSelect('state')
                ->addAttributeToSelect('increment_id')
                ->addAttributeToSelect('customer_is_guest')
                ->addAttributeToSelect('customer_id')
                ->addAttributeToSelect('customer_taxvat')
                ->addAttributeToSelect('created_at')
                ->addAttributeToSelect('base_grand_total')
                ->addAttributeToSelect('shipping_method')
                ->addAttributeToSelect('base_grand_total')
                ->addAttributeToSelect('base_shipping_amount')
                ->addAttributeToSelect('base_discount_amount')       
                ->addAttributeToSelect('quote_id')       
                ->join(
                    array('core_store' => 'core/store'), 'main_table.store_id = core_store.store_id', array('core_store.store_id AS store_id') 
                )         
                ->join(
                    array('core_website' => 'core/website'), 'core_store.website_id = core_website.website_id', array('core_website.code AS website_code') 
                )         
                ->join(
                    array('shipping_address' => 'sales/order_address'), 'main_table.entity_id = shipping_address.parent_id AND shipping_address.address_type = "shipping"', $shipping_address_fields
                    
                )              
                ->join(
                    array('billing_address' => 'sales/order_address'), 'main_table.entity_id = billing_address.parent_id AND billing_address.address_type = "billing"', $billing_address_fiels
                )                
                //->addFieldToFilter('increment_id', array('eq' => '1011200002223'))
                ->addFieldToFilter('status', array('in' => $fromStatuses));
          //echo $orders ->getSelect();exit;         
        return $orders;
    }

    public function getCsvFileToErp() {
        
        $regions = Mage::getModel('directory/region')->getResourceCollection()->load();
        $start_time = time();
        $website_code = 'base'; //ver
        $helper = Mage::helper('winwin_opsintegration/data');

        $fromStatuses = $helper->getOrderToErp($website_code);
        $fromStatuses = explode(',', $fromStatuses);
        $this->_exportCollectionLimit = $helper->getOrderExportCollectionLimit($website_code);
        $this->_exportPageSize = $helper->getOrderExportPageLimit($website_code);
        if ($this->_exportCollectionLimit < $this->_exportPageSize) {
            $this->_exportPageSize = $this->_exportCollectionLimit;
        }

        $defaultFolder  = $helper->getDirectoryLocation($website_code);        
        $delimiter      = $helper->getDelimiter($website_code);
        $enclosure      = $helper->getEnclosure($website_code);
        $toStatus       = $helper->getOrderToErpAfter($website_code); 
        $statuses       = Mage::getResourceModel('sales/order_status_collection')
                            ->joinStates()
                            ->addFieldToFilter('main_table.status', $toStatus)
                            ->getFirstItem();
                 
        $toState        = $statuses->getState();
        
        $debug_mode      = $helper->getDebugMode($website_code); 
        $upload_ftp      = $helper->getExportOrderToFTP($website_code); 
        $change_status   = $helper->getChangeOrderToErpAfter($website_code); 
        $ftp_outgoing    = $helper->getFTPOutgoingDirectory($website_code);
        $dni_in_shipping = $helper->getDniField($website_code);

        $file_name_code  = $helper->getOrderFileNameCode($website_code);
        
        $file = null;
        $originalCollection = $this->getCollection($fromStatuses, $dni_in_shipping); //->getSelect()->limit($this->_exportPageLimit);        
        $_count = $originalCollection->getSize();        
        
        $io = new Varien_Io_File();
        $_executedTimestamp = gmdate('YmdHis');
        $_executedTimestampDb = gmdate("Y-m-d H:i:s");
        $path = Mage::getBaseDir('base') . DS . $defaultFolder . DS . 'Outbound' . DS . 'Pending' . DS;
        $file_name_code = ( trim($file_name_code) != "" ) ?  '-'. $file_name_code . '-' : '-';             
        $name = 'orders' . $file_name_code . $_executedTimestamp . '.csv'; 
        $file = $path . $name;            
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $path));
        $io->streamOpen($file, 'w+');
        $io->streamLock(true);

        $count = null;
        $page = 1;
        $lPage = null;
        $break = false;
        $lastPageSize = false;
        $lastPageNumberOffset = false;
        $_recordProcessedCorrectly = 0;
        $_totalRecords = 0;

        while ($break !== true) {
            $_errorLogsArr = array();
            $collection = clone $originalCollection;
            if ($lPage == $page && $lastPageSize && $lastPageNumberOffset) {
                $collection->getSelect()->limit($lastPageSize, $lastPageNumberOffset);
            } else {
                $collection->setPageSize($this->_exportPageSize);
                $collection->setCurPage($page);
            }

            $collection->load();
            if (is_null($count)) {
                $count = $collection->getSize();
                $lPage = $collection->getLastPageNumber();
                if ($count > $this->_exportCollectionLimit) {
                    $lPage = ceil($this->_exportCollectionLimit / $this->_exportPageSize);
                    $lastPageSize = $this->_exportPageSize - ($lPage * $this->_exportPageSize - $this->_exportCollectionLimit);
                    $lastPageNumberOffset = $this->_exportCollectionLimit - $lastPageSize;
                }
            }

            if ($lPage == $page) {
                $break = true;
            }
            $page++;

            foreach ($collection as $order) {
                $orderRows = array();
                if (!in_array($order->getIncrementId(), $this->_skipIncrementId)) {

                    // inicializo todas las variables
                    $interestValue  = 0;
                    $cuotaValue     = 1;
                    $tarjetaValue   = '';                  
                    $bancoValue     = '';
                    $transaction    = '';
                    $couponCode     = '';
                    $authCode       = '';
                    $BINNumber      = '';
                    
                    //instantiate data for export
                    $payment = $order->getPayment()->getMethod();

                    $conf = Mage::getSingleton('core/config')->init()->getXpath('global/medios_pago//code[.="' . $payment . '"]/..');
                    
                    if ($conf) {
                        $conf    = new Varien_Object(current($conf)->asArray());                        
                        $interes = new Varien_Object($conf->getInteres());                                                
                        $cuota   = new Varien_Object($conf->getCuota());
                        $tarjeta = new Varien_Object($conf->getTarjeta());     
                        $banco   = new Varien_Object($conf->getBanco());                       
                        $auth    = new Varien_Object($conf->getAuth());                       
                        $bin     = new Varien_Object($conf->getBin());                       


                        if (method_exists(Mage::getModel($interes->getModel()), $interes->getMethod())) {
                            $interestValue = Mage::getModel($interes->getModel())->{$interes->getMethod()}($order);
                        }
                        if (method_exists(Mage::getModel($cuota->getModel()), $cuota->getMethod())) {
                            $cuotaValue = Mage::getModel($cuota->getModel())->{$cuota->getMethod()}($order);
                        }
                        if (method_exists(Mage::getModel($tarjeta->getModel()), $tarjeta->getMethod())) {
                            $tarjetaValue = Mage::getModel($tarjeta->getModel())->{$tarjeta->getMethod()}($order);
                        }
                        if (method_exists(Mage::getModel($auth->getModel()), $auth->getMethod())) {
                            $authCode = Mage::getModel($auth->getModel())->{$auth->getMethod()}($order);
                        }
                        if (method_exists(Mage::getModel($banco->getModel()), $banco->getMethod())) {
                            $bancoValue = Mage::getModel($banco->getModel())->{$banco->getMethod()}($order);
                        }
                        if (method_exists(Mage::getModel($bin->getModel()), $bin->getMethod())) {
                            $BINNumber = Mage::getModel($bin->getModel())->{$bin->getMethod()}($order);
                        }
                    }

                    if($order->getCustomerIsGuest()){
                        
                        if ($debug_mode) Mage::log('Es una compra de usuario invitado', Zend_Log::DEBUG, 'int_debug.log');

                        $dni = $order->getData('customer_taxvat');
                        $customerEmail = $order->getCustomerEmail();

                    }else{

                        if ($debug_mode) Mage::log('Es una compra de usuario registrado', Zend_Log::DEBUG, 'int_debug.log');

                        $customer = Mage::getModel('customer/customer'); //new Mage_Customer_Model_Customer();
                        $customer->load($order->getCustomerId());                    
                        $customerEmail = $customer->getEmail();

                        if ($dni_in_shipping){
                            $dni = $order->getCustomerDni();
                        }else{
                            $dni = $customer->getDni();     
                            if (! $dni ) $dni = $customer->getTaxvat();
                        }      

                    }
                    
                    #1
                    $extra_data = $helper->getOrderExtraData($order->getWebsiteCode());                    
                    $row = array(
                        $order->getIncrementId(),   //nro de orden
                        $order->getCustomerId(),    //nro de cliente
                        $dni,
                        ($customerEmail) ? $customerEmail : $order->getCustomerEmail(),
                        date('Y/m/d', strtotime($order->getCreatedAt())), //fecha
                        sprintf("%01.2f", $order->getBaseGrandTotal()), //total
                        $payment,
                        $tarjetaValue,  /* .'/MEDIO_PAGO(RT01)' */ 
                        $bancoValue,
                        $cuotaValue,   /* .'/CUOTAS(RT01)' */
                        $BINNumber,                        
                        $couponCode,  // cupon de transaccion (sin el lote)
                        $authCode    // nro de autorización                        
                    );
                    $row = array_merge($row,$extra_data);

                    if ($debug_mode) Mage::log(print_r($row,true), Zend_Log::DEBUG, 'int_debug.log');
                                    
                    $orderRows[1] = $row;

                    #2
                    $street = $this->cleanStreet($order->getBillingStreet());

                    $country_iso_code = $order->getBillingCountryId();

		            $region = $regions->getItemByColumnValue('region_id',$order->getBillingRegionId());
                    $region_iso_code = '';
                    if ($region) $region_iso_code  = $country_iso_code . '-' . $region->getCode();

                    $row = array($order->getIncrementId(),
                        $order->getBillingFirstname(),
                        $order->getBillingLastname(),
                        $street,
                        $order->getBillingPostcode(),
                        $order->getBillingCity(),
                        $region->getName(),
                        $country_iso_code,
                        $order->getBillingTelephone(),
                        $order->getBillingFax());

                    if ($debug_mode) Mage::log(print_r($row,true), Zend_Log::DEBUG, 'int_debug.log');
                    
                    $orderRows[2] = $row;

                    #3
                    $street = $this->cleanStreet($order->getShippingStreet());

                    $country_iso_code = $order->getShippingCountryId();
		            $region = $regions->getItemByColumnValue('region_id',$order->getShippingRegionId());
                    $region_iso_code = '';
                    if ($region) $region_iso_code  = $country_iso_code . '-' . $region->getCode();
	
                    $row = array($order->getIncrementId(), 
                        $order->getShippingFirstname(),
                        $order->getShippingLastname(),
                        $street,
                        $order->getShippingPostcode(),
                        $order->getShippingCity(),
                        $region->getName(),
                        $country_iso_code,
                        $order->getShippingTelephone(),
                        $order->getShippingFax());

                    if ($debug_mode) Mage::log(print_r($row,true), Zend_Log::DEBUG, 'int_debug.log');
                    
                    $orderRows[3] = $row;
                    
                    #4
                    $row = array($order->getIncrementId(), $order->getShippingMethod(), sprintf("%01.2f", $order->getBaseShippingAmount()));
                    if ($debug_mode) Mage::log(print_r($row,true), Zend_Log::DEBUG, 'int_debug.log');
                    
                    $orderRows[4] = $row;
                    
                    #5
                    $row = array($order->getIncrementId(), sprintf("%01.2f", $order->getBaseDiscountAmount()));
                    if ($debug_mode) Mage::log(print_r($row,true), Zend_Log::DEBUG, 'int_debug.log');
                    
                    $orderRows[5] = $row;
                    
                    #6
                    $row = array($order->getIncrementId(), sprintf("%01.2f", $interestValue)/* .'/VALOR_INTERES(RT01)' */);
                    if ($debug_mode) Mage::log(print_r($row,true), Zend_Log::DEBUG, 'int_debug.log');
                    
                    $orderRows[6] = $row;

                    #7
                    //Agregado compatibilidad con el modulo de Store Credit de AheadWorks
                    $storeCreditAmount = '0.00';
                    if (Mage::getConfig()->getNode('modules/AW_Storecredit')){
                         $storeCredit = Mage::helper('aw_storecredit/totals')->getQuoteStoreCredit($order->getQuoteId());
                         if (count($storeCredit) > 0){                                                    
                            $storeCredit = reset($storeCredit);                            
                            $storeCreditAmount = sprintf("%01.2f", -$storeCredit->getData('storecredit_amount'));                                                                                        
                         }
                         
                    }
                    $row = array($order->getIncrementId(), $storeCreditAmount);
                    if ($debug_mode) Mage::log(print_r($row,true), Zend_Log::DEBUG, 'int_debug.log');                    
                                    
                    $orderRows[7] = $row;

                    #8 to N
                    $orderItems = $order->getAllVisibleItems();
                    $orderItemsIterator = 1;
                    foreach ($orderItems as $orderItem) {
                        
                        
                        $_price = $orderItem->getBasePrice();
                        if($_price == 0) $_price = '0.01';

                        $row = array($order->getIncrementId(), $orderItem->getSku(), $orderItem->getName(), sprintf("%d", $orderItem->getQtyOrdered()), sprintf("%01.2f", $_price));                          
                        $orderRows[8][$orderItemsIterator] = $row;
                        $orderItemsIterator++;

                        
                    }

                    $writeRow = true;

                    if ($change_status){
                        try{                            
                            $order->setState($toState, $toStatus, 'ORDER EXPORT', false); //for states
                            $order->save();
                        }catch (Exception $e) {
                            $this->_skipIncrementId[] = $order->getIncrementId();                        
                            $log = $e->getMessage() . " Unable to update order status with state: " . $order->getState() . ", status from: " . $order->getStatus() . " to: " . $toStatus . " , order#: " . $order->getIncrementId();
                            $_errorLogsArr[] = $log;
                            if ($debug_mode) Mage::log($log, Zend_Log::DEBUG, 'int_debug.log');                    
                            $writeRow = false;
                        }
                    }
                    if ($writeRow){

                        for($orderRowsIterator = 1; $orderRowsIterator < 8; $orderRowsIterator++){
                            if($enclosure != '') {
                                $io->streamWriteCsv($orderRows[$orderRowsIterator], $delimiter, $enclosure);
                            }else{
                               $io->streamWrite(implode($orderRows[$orderRowsIterator], $delimiter)."\n");
                            }
                        }
                        for($orderItemsIterator = 1; $orderItemsIterator <= count($orderRows[8]); $orderItemsIterator++){
                            if($enclosure != '') {
                                $io->streamWriteCsv($orderRows[8][$orderItemsIterator], $delimiter, $enclosure);
                            }else{
                               $io->streamWrite(implode($orderRows[8][$orderItemsIterator], $delimiter)."\n");
                            }   
                        }

                        $_recordProcessedCorrectly++;
                        if ($debug_mode) Mage::log('ORDER ID: ' . $order->getIncrementId(), Zend_Log::DEBUG, 'int_debug.log');
                    }
                    $_totalRecords++;
                }
            }
        }
        
        $_dispatch = true;
        if ($_totalRecords === 0) {
            try {                
                $io->rm($name);
                $_dispatch = false;
            } catch (Exception $e) {
                $_errorLogsArr[] = $e->getMessage() . " Unable to delete file: " . $file;
            }
        }
        $io->streamUnlock();
    
        $fileMoved = 'NO';
        if ($_dispatch) {

            if ($upload_ftp){
                $ftp_host = $helper->getFTPHost($website_code);
                $ftp_user = $helper->getFTPUser($website_code);
                $ftp_pass = $helper->getFTPPassword($website_code);
                $connect_string = $helper->getFTPConnectioString($ftp_host, $ftp_user, $ftp_pass);

                try{
                    $ftp_handler = Mage::getModel('winwin_opsintegration/lib_ftp');                                        
                    $ftp_handler->connect($connect_string);                    
                    $fileUploaded = $ftp_handler->upload($ftp_outgoing . DS . $name, $file);                                        
                } catch (Exception $e) {                    
                    Mage::log('ERROR UPLOADING FTP: ' . $e->getMessage() , 6, 'int.log');                    
                    $fileUploaded = false;
                }   
                @$ftp_handler->close();

                $fileMoved = 'ERROR';
                if ($fileUploaded){
                    $moveFileToPath = Mage::getBaseDir('base') . DS . $defaultFolder . DS . 'Outbound' . DS . 'Processed' . DS;
                    $fileMoved = 'OK';
                    try {
                        $io->checkAndCreateFolder($moveFileToPath);
                        $io->mv($file, $moveFileToPath . $name);                        
                        
                    } catch (Exception $e) {
                        $fileMoved = 'ERROR';
                        $_errorLogsArr[] = 'File moved ERROR, ' . $file . ', PHP Exception: ' . $e->getMessage();
                        Mage::log('CANT MOVE FILE ' . $e->getMessage(), 6, 'int.log');
                    }            
                }

            }
            $io->streamClose();
            
            $_executionStatus = (count($_errorLogsArr) === 0) ? 'successful' : 'error';
            Mage::dispatchEvent(
                    'winwin_opsintegration_integration_execution', array(
                'rf04' =>
                array(
                    'integration_name' => 'Ordenes_Export', /* Precios_Import / Stocks_Import / Ordenes_Export */
                    'executed_at' => $_executedTimestampDb,
                    'processed_file_name' => $name,
                    'records_processed_correctly' => $_recordProcessedCorrectly,
                    'total_records' => $_totalRecords,
                    'execution_type' => $helper->_winwinUserIs, /* manual / automatic) */
                    'username' => $helper->getUser(), /* only if was executed manually */
                    'execution_status' => $_executionStatus, /* 'successful' or 'error' */
                ),
                'rf03' => $_errorLogsArr,
                'store_id' => null,
                'website_code' => $website_code,
                'file' => $file,
                'file_name' => $name,
                'executed_timestamp' => $_executedTimestamp,
                'cost_time' => gmdate("H:i:s", time() - $start_time),
                'log_filename' => 'integracion_ordenes.log', /* 'integracion_stock or integracion_precios or integracion_ordenes.log' */
                'log_error' => 'errores_ordenes.log', /* errores_stock.log or errores_precios.log or errores_ordenes.log */
                'file_moved' => $fileMoved,
                    )
            );                      
        }
        
    }
}
