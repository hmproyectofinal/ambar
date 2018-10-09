<?php
/**
 * NPS PSP
 * @version 2.2
 */
class Hd_Nps_Model_Psp extends Mage_Payment_Model_Method_Abstract
{

    const TRANSACTION_TYPE_PAY_ONLINE   = 'pay_online';
    
    const TRANSACTION_TYPE_AUTHORIZE    = 'authorize';
    
    
    const ORDER_STATUS_PENDING          = 'pending_nps';
    
    const ORDER_STATUS_CANCELED         = 'canceled_nps';
    
    const ORDER_STATUS_PROCESSED        = 'processed_nps';
    
    const ORDER_STATUS_HOLDED_AVS       = 'holded_avs_nps';
    
    const ORDER_STATUS_HOLDED_FRAUD     = 'holded_fraud_nps';
    
    
    /**
     * Cliente Soap
     */
    protected $_soapClient;
    
    /**
     * Source Model (factory path)
     * @var String
     */
    protected $_paymentSource;
    
    /**
     * Path Completo a la configuracion de la URL del WSDL
     * @var string 
     */
    protected $_xmlConfigPathSoapWsdlUrl;
    

    /**************************************************************************/
    /******************  Mage_Payment_Model_Method_Abstract - IMPLEMENTATION **/
    /**************************************************************************/
   
    public function authorize(\Varien_Object $payment, $amount)
    {
        /* @var $payment Mage_Sales_Model_Order_Payment */
        
        $this->dLog(__METHOD__ . " Amount: {$amount}");
        
        try {
            
            $rawData = $this->loadPayOnline3pFormData($payment->getOrder())
                ->getPayOnline3pFormData(true);
            
            // Prepare Data
            $data = $this->_prepareRawData($rawData);
           
            $pspTransactionId = $data['psp_TransactionId'];

            $payment->setIsTransactionClosed(false);
            $payment->setTransactionId('authorize_' . $pspTransactionId);            
            $payment->setTransactionAdditionalInfo(
               Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS
               ,$data
            );

            // Very Very Important!!!
            $this->_getCheckoutSession()
                ->setNpsQuoteId($payment->getOrder()->getQuoteId());
            
        } catch (Exception $e) {
            $message = ($this->isDebug()) ? $e->getMessage()
                : $this->_helper()->__('Unable to connect with NPS.');
            throw new Hd_Nps_Exception($message);
        }
        
        return $this;
    }
    
    public function order(\Varien_Object $payment, $amount)
    {
        /* @var $payment Mage_Sales_Model_Order_Payment */
        
        $this->dLog(__METHOD__ . " Amount: {$amount}");
        
        try {
            
            $rawData = $this->loadPayOnline3pFormData($payment->getOrder())
                ->getPayOnline3pFormData(true);
            
            // Prepare Data
            $data = $this->_prepareRawData($rawData);
           
            $pspTransactionId = $data['psp_TransactionId'];

            $payment->setIsTransactionClosed(false);
            $payment->setTransactionId('order_' . $pspTransactionId);            
            $payment->setTransactionAdditionalInfo(
               Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS
               ,$data
            );

            // Very Very Important!!!
            $this->_getCheckoutSession()
                ->setNpsQuoteId($payment->getOrder()->getQuoteId());
            
        } catch (Exception $e) {
            $message = ($this->isDebug()) ? $e->getMessage()
                : $this->_helper()->__('Unable to connect with NPS.');
            throw new Hd_Nps_Exception($message);
        }
        
        return $this;
    }
    
    public function capture(\Varien_Object $payment, $amount)
    {
        $this->dLog(__METHOD__ . " Amount: {$amount}");
        
        $data = $this->_prepareRawData($this->getPayOnline3pTransactionData());
        
        $pspTransactionId = $data['psp_TransactionId'];
        
        $payment->setIsTransactionClosed(false);
        $payment->setTransactionId('capture_' . $pspTransactionId);
        $payment->setParentTransactionId('authorize_' . $pspTransactionId);
        $payment->setTransactionAdditionalInfo(
           Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS
           ,$data
        );
        
        return $this;
    }
    
    public function void(\Varien_Object $payment)
    {
        $this->dLog(__METHOD__);
    }
    
    public function canFetchTransactionInfo()
    {
        return $this->isDebug();
    }
    
    public function fetchTransactionInfo(\Mage_Payment_Model_Info $payment, $transactionId)
    {
        $txInfo = explode('_', $transactionId);
//        var_dump($txInfo);
        switch ($txInfo[0]) {
            case 'order':
            case 'authorize':
                $data = $this->_prepareRawData($this->getPayOnline3pFormData(true));
                break;
            case 'capture':
            case 'void':
                $data = $this->_prepareRawData($this->getPayOnline3pTransactionData());
                break;
            default:
                $data = array(
                    'transactionId' => $transactionId,
                );
                break;
        }
        return $data;
    }
    
    protected function _prepareRawData($data)
    {
        $rawData = (array)$data;
        foreach($rawData as $k => $v) {
            if (is_object($v) || is_array($v)) {
                $rawData[$k] = json_encode($v);
//                $rawData[$k] = serialize($v);
            }
        }
        return $rawData;
    }
    
    /**
     * Returns Redirection URL
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->_getActionUrl();
    }
    
    /**
     * Default Product CreditCards
     * @todo Implement in child clases for other Product Types
     */
    public function canUseForCountry($country)
    {
        // Validate Store Countries VS Product
        if(!$this->countryHasProduct($this->_getCountryCode(), 'cc')) {
            return false;
        }
        // Validate Payments Availability
        return ($this->getPaymentSource()->hasPaymentOptions())
            ? parent::canUseForCountry($country) // Customer Country Validation
            : false;
    }
    
    /**
     * @todo Implementar Validación de Currency (cc)
     * 
     * @param type $currencyCode
     */    
    public function canUseForCurrency($currencyCode)
    {
        $result = $this->countryHasCurrency($this->_getCountryCode(), $currencyCode);
        return $result;
    }
    
        
    /**************************************************************************/
    /************************************************ PUBLIC USEFULL METHODS **/
    /**************************************************************************/
    
    /**
     * Returns Debug Mode Status
     * @return bool
     */
    public function isDebug()
    {
        return (bool)$this->getConfigData('debug_mode');
    }
    
    public function dLog($data)
    {
        if(!$this->isDebug()) {
            return $this;
        }
        
        Mage::log($data,7,'nps-psp-debug.log');
        
        return $this;
    }
    
    /**
     * Returns Wsdl Url
     * @return type
     */
    public function getXmlConfigPathSoapWsdlUrl()
    {
        return $this->_xmlConfigPathSoapWsdlUrl;
    }
    
    /**
     * Returns the Transaction Type Code
     * @return string
     */
    public function getTransactionType()
    {
        return $this->getConfigData('transaction_type');
    }
    
    public function getPspCountry($code)
    {
        return (isset($this->_pspCountry[$code])) ? $this->_pspCountry[$code] : null;
    }
    
    
    /**************************************************************************/
    /***************************************** SOAP RELATED - PUBLIC METHODS **/
    /**************************************************************************/
    
    
    /**
     * Executes "PayOnLine_3p" Soap Request for given $order
     * and saves result in the OrderPayment Object
     * 
     * @param Mage_Sales_Model_Order $order
     */
    public function loadPayOnline3pFormData(Mage_Sales_Model_Order $order = null)
    {
        $requestParams = $this->_getPayOnline3pRequestData();
        $result = $this->_getSoapClient()->doPayOnline3pRequest($requestParams);
        
        // Validacion
        if (!$result->getStatus()) {
            throw new Hd_Nps_Exception($this->_helper()->__($result->getMessage()));
        }
        
        if(!$response = $result->getResponse()) {
            throw new Hd_Nps_Exception($this->_helper()->__('NPS Conecction Error: No data received.'));
        }
        
        // Serialize
        $data = serialize($response);
        
        // Save to Quote
        $this->getInfoInstance()
            ->setData('nps_payonline_form_data', $data);
        
        // Save to Order
        if ($order) {
            $order->getPayment()
                ->setData('nps_payonline_form_data',$data);
        }
        return $this;
    }
  
    /**
     * Execute SimpleQueryTx Soap Request for the given transactionId 
     * and saves result in the OrderPayment Object
     * 
     * @param int $transactionId
     */
    public function loadPayOnlyne3pTransactionData($transactionId)
    {
        $params = array(
            "psp_Version"         => $this->_getPspVersion(),
            "psp_MerchantId"      => $this->_getPspMerchantId(),
            "psp_QueryCriteria"   => "T",
            "psp_QueryCriteriaId" => $transactionId,
            "psp_PosDateTime"     => $this->_getPspPosDateTime(),
        );
        // Hash
        $params["psp_SecureHash"] = $this->_getPspSecureHash($params, true);
        // Sort
        ksort($params);
        
        $result = $this->_getSoapClient()->doSimpleQueryTx($params);
        
        // Validacion
        if (!$response = $result->getResponse()) {
            Mage::throwException($this->_helper()->__('Unable to connect with Payment Service.'));
        }
        
        if (isset($response->psp_Transaction)) {
            $data = array(
                'nps_payonline_transaction_data' => serialize($response->psp_Transaction),
            );
            $this->getInfoInstance()
                ->addData($data)
                ->save();
        }
        
        return $result;
                
    }
    
    /**************************************************************************/
    /******************************* TRANSACION DATA - PUBLIC GETTER METHODS **/
    /**************************************************************************/
    
        
    /**
     * Returns the PaymentRequest saved data from the Payment Object 
     * 
     * @param $completeData | If true returns all data else just form needed 
     * @return array
     */
    public function getPayOnline3pFormData($completeData = false)
    {
        $response = $this->getInfoInstance()->getData('nps_payonline_form_data');
        
        if (!$response) {
            Mage::throwException($this->_helper()->__(
                "Unable to get redirect form data. for order: {$this->_getOrder()->getId()}"
            ));
        }
        
        $params = unserialize($response);
        
        if ($completeData) {
            return $params;
        }
        
        $data = array(
            "action"  => $params->psp_FrontPSP_URL,
            "fields"  => array (
                "psp_TransactionId" => $params->psp_TransactionId,
                "psp_Session3p"     => $params->psp_Session3p,
                "psp_MerchTxRef"    => $params->psp_MerchTxRef,
                "psp_MerchantId"    => $params->psp_MerchantId,
            )
        );
        
        return $data;
    }
  
    
    /**
     * Returns PayOnline 3p Transaction Saved Data
     * 
     * @return stdClass
     */
    public function getPayOnline3pTransactionData($transactionId = null)
    {
        $data = $this->getInfoInstance()->getData('nps_payonline_transaction_data');
        
        if (!$data && $transactionId) {
            $this->loadPayOnlyne3pTransactionData($transactionId);
        }
        
        if (!$data = $this->getInfoInstance()->getData('nps_payonline_transaction_data')) {
            Mage::throwException($this->_helper()->__('Unable to get transaction data.'));
        }
        
        return unserialize($data);
    }
    
    
    /**
     * Resturns the pspTransactionId . T
     * @return int
     */
    public function getOrderPspTransactionId()
    {
        if(!$this->_getOrder()) {
            Mage::throwException($this->_helper()->__('Unable to execute method in current context'));
        }
        
        $formData = (array)$this->getPayOnline3pFormData(true);
            
        $transactionId = (isset($formData['psp_TransactionId'])) 
            ? $formData['psp_TransactionId'] : null;

        if(!$transactionId) {
            Mage::throwException($this->_helper()->__('Unable to find NPS Transaction ID'));
        }
        
        return $transactionId;
        
    }
    

    
    
    /**************************************************************************/
    /********************************************** PROCESS - PUBLIC METHODS **/
    /**************************************************************************/
    
    /**
     * Performs Order & Payment Cancelation if customer decline payment
     * 
     * @param string $message
     * @return \Hd_Nps_Model_Psp
     */
    public function processCancelAction($message = null)
    {
        $this->_setOrderToPaymentCanceled($message);
        return $this;
    }
        
    /**
     * Performs the Evaluation of the payment transaction result
     * 
     * @param int $transactionId
     * @return boolean
     */
    public function processEvaluateAction($transactionId = null)
    {
        try {
            
            $transactionId = ($transactionId) ?: $this->getOrderPspTransactionId();
            
            if(!$transactionId) {
                Mage::throwException($this->_helper()->__('Unable to find NPS Transaction ID'));
            }
            
            $result  = $this->loadPayOnlyne3pTransactionData($transactionId);
            $data    = $this->getPayOnline3pTransactionData();
            $message = $result->getMessage();
            
            // Ok
            if ($result->getStatus() === true) {
                
                // Order Processed
                $this->_setOrderToPaymentProcessed($message);
                
                // Add custom 'payment' Transaction ¿?
                
                // Invoice
                if((bool)$this->getConfigData('invoice_active')) {
                    $this->_setOrderInvoice($data);
                }                

                // AVS Mock Data
                if($this->isDebug() && $this->_isAvsVisaActive() && empty($data->psp_VisaArg_DA_Result)) {
                    $data->psp_VisaArg_DA_Result = $this->getConfigData('debug_avs_visa_mock');
                }                
                // Validate AVS
                if(!$this->_validateAvs($data)) {
                    $this->_setOrderToHoldAvs($this->_avsValidationData['message']);
                }
                // Save AVS Data
                if(count($this->_avsValidationData)) {
                    $this->getInfoInstance()
                        ->setData('nps_payonline_avs_data', serialize($this->_avsValidationData))
                        ->save();
                }

                // Fraud Mock Data
                if($this->isDebug() && $this->_isFraudEvaluationActive() && (bool)$this->getConfigData('debug_fraud_fail')) {
                    $data->psp_FraudScreeningResult->ResultCode = 'R';
                }
                // Validate Fraud
                if(!$this->_validateFraud($data)) {
                    $this->_setOrderToHoldFraud($this->_fraudValidationData['message']);
                }
                // Save Fraud Data
                if(count($this->_fraudValidationData)) {
                    $this->getInfoInstance()
                        ->setData('nps_payonline_fraud_data', serialize($this->_fraudValidationData))
                        ->save();
                }
                
                return true;
                
            }
            
            // Error
            $this->_setOrderToPaymentCanceled($message);
            return false;
            
            
        } catch (Hd_Nps_Exception $e) {
            
            Mage::logException($e);
            // Caso: Error de conexión
            return false;
            
        } catch (Exception $e) {
            Mage::logException($e);
            // Caso: Error de conexión
            return false;
        }

    }
  
    
    /**
     * Tries to recover transaction result and process it acoords to it result
     * - This method must be called within the order object:
     * 
     *  Mage::getModel('sales/order')->load(id)
     *      ->getPayment()->getMethodInstance()->processCronAction();
     * 
     * @return boolean
     */
    public function processCronAction()
    {
        return $this->processEvaluateAction();       
    }
    
    protected $_fraudProcessor;
    
    /**
     * @return Hd_Nps_Model_Psp_Fraud_Processor
     */
    public function getFraudProcessor()
    {
        if(!$this->_fraudProcessor) {
            $order = $this->_getOrder();
            if(!$order) {
                Mage::throwException('Implemantation Error. Fraud Processor Must be intantiated within order context.');
            }
            $this->_fraudProcessor = Mage::getModel('hd_nps/psp_fraud_processor', array('order' => $order, 'psp' => $this));
        }
        return $this->_fraudProcessor;
    }
    
    
    /**************************************************************************/
    /********************* SOAP RESPONSE DATA VALIDATION - PROTECTED METHODS **/
    /**************************************************************************/
    
    protected $_avsValidationData = array();
    
    protected function _validateAvs($data)
    {
        $avsTypes = array(
            '14'  => 'Visa',
            '1'   => 'Amex',
            '5'   => 'Master',
        );
        
        // No AVS for Product
        $product = $data->psp_Product;
        if(!isset($avsTypes[$product])) {
            $this->dLog(__METHOD__ . " | No AVS for Product {$product}");
            return true;
        }
        
        // Validation
        $validator = "_validateAvs{$avsTypes[$product]}";
        return $this->$validator($data);
        
    }
    
    protected function _validateAvsVisa($data)
    {
        $this->dLog(__METHOD__ . " | INIT VALIDATION");
        
        if(!$this->_isAvsVisaActive()) {
            $this->dLog(__METHOD__ . " | SKIP NO ACTIVE");
            return true;
        }
        
        if($data->psp_Country != 'ARG') {
            $this->dLog(__METHOD__ . " | SKIP NO ARG");
            return true;
        }
        
        if(empty($data->psp_VisaArg_DA_Result) || strlen($data->psp_VisaArg_DA_Result) < 5) {
            
            $this->dLog(__METHOD__ . " | ERROR: NO AVS DATA");            
            $this->_avsValidationData['message'] = $this->_helper()->__(
                "No se recibió el resultado de Datos Adicionales VISA."
            );
            return false;
        }
        
        $nData = array(
            array(
                'name' => "Tipo de Documento",
                'check' => (int)$this->getConfigData('avs_visa_n_0'),
            ),
            array(
                'name' => "Número de Documento",
                'check' => (int)$this->getConfigData('avs_visa_n_1'),
            ),
            array(
                'name' => "Número de Puerta",
                'check' => (int)$this->getConfigData('avs_visa_n_2'),
            ),
            array(
                'name' => "Fecha de Nacimiento",
                'check' => (int)$this->getConfigData('avs_visa_n_3'),
            ),
            array(
                'name' => "Nombre",
                'check' => (int)$this->getConfigData('avs_visa_n_4'),
            ),
        );
        
        $nStatus = array('Correcto','Incorrecto','No Validado');
        $result  = true;
        
        foreach(str_split((string)$data->psp_VisaArg_DA_Result) as $k => $v) {
            
            // Set Data
            $nData[$k]['value']  = $v;
            $nData[$k]['status'] = $nStatus[$v];
            // Check        
            if((bool)$nData[$k]['check'] && (int)$v == 1) {
                $result = false;
            }
        }
        
        $message = ($result) ? 'AVS Visa: Valicación Exitosa'
            : 'AVS Visa: Validación Con Erores';
        
        // Append Message
        $this->_avsValidationData['result']  = $result;
        $this->_avsValidationData['fields']  = $nData;
        $this->_avsValidationData['message'] = $message;
        
        $this->dLog(__METHOD__ . " | RESULT");
        $this->dLog($this->_avsValidationData);
        
        return $result;
        
    }
    
    protected function _validateAvsAmex($data)
    {
        $this->dLog(__METHOD__ . " | INIT VALIDATION");
        if(!$this->_isAvsAmexActive()) {
            // Implement
            $this->dLog(__METHOD__ . " | SKIP NO ACTIVE");
        }
        return true;
    }
    
    protected function _validateAvsMaster($data)
    {
        $this->dLog(__METHOD__ . " | INIT VALIDATION");
        if(!$this->_isAvsMasterActive()) {
            // Implement
            $this->dLog(__METHOD__ . " | SKIP NO ACTIVE");
        }
        return true;
    }

    protected $_fraudValidationData;
    
    protected function _validateFraud($data)
    {
        $this->dLog(__METHOD__ . " | INIT VALIDATION");
        if(!$this->_isFraudEvaluationActive()) {
            $this->dLog(__METHOD__ . " | SKIP NO ACTIVE");
            return true;
        }
        if(empty($data->psp_FraudScreeningResult)) {
            $this->dLog(__METHOD__ . " | SKIP NO FRAUD EVALUATION DATA");
            return true;
        }
        
        $code = $data->psp_FraudScreeningResult->ResultCode;
        $message = $data->psp_FraudScreeningResult->ResultDescription;
        $result = !($code == 'D' || $code == 'R');
        
        $this->_fraudValidationData = array(
            'code' => $code,
            'message' => $message,
            'result' => $result,
        );
        
        // @todo
        // Add Fraud Data
        
        $this->dLog(__METHOD__ . " | STATUS: " . (int)$result); 
        $this->dLog(__METHOD__ . " | DATA: ");
        $this->dLog($this->_fraudValidationData);
        
        return $result;
        
    }

    /**************************************************************************/
    /********************************** ORDER PROCESSING - PROTECTED METHODS **/
    /**************************************************************************/

    protected function _orderCanBeCanceled($order)
    {
        switch ($order->getState()) {
            case Mage_Sales_Model_Order::STATE_CANCELED:
            case Mage_Sales_Model_Order::STATE_COMPLETE:
            case Mage_Sales_Model_Order::STATE_CLOSED:
                $result = false;
                break;
            case Mage_Sales_Model_Order::STATE_NEW:
            case Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW:
            case Mage_Sales_Model_Order::STATE_PENDING_PAYMENT:
            case Mage_Sales_Model_Order::STATE_HOLDED:
            default:
                $result = true;
                break;
        }
        if($order->getStatus() == self::ORDER_STATUS_PROCESSED) {
            $result = false;
        }
        return $result;
    }
    
    protected function _orderCanBeProcessed($order)
    {
        switch ($order->getState()) {
            case Mage_Sales_Model_Order::STATE_CANCELED:
            // case Mage_Sales_Model_Order::STATE_PROCESSING:
            case Mage_Sales_Model_Order::STATE_COMPLETE:
            case Mage_Sales_Model_Order::STATE_CLOSED:
                $result = false;
                break;
            case Mage_Sales_Model_Order::STATE_NEW:
            case Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW:
            case Mage_Sales_Model_Order::STATE_PENDING_PAYMENT:
            case Mage_Sales_Model_Order::STATE_HOLDED:
            default:
                $result = true;
                break;
        }
        
        if($order->getStatus() != self::ORDER_STATUS_PENDING) {
            $result = false;
        }
        
        return $result;
    }
    
    
    protected function _setOrderToPaymentProcessed($comment = null,  Mage_Sales_Model_Order $order = null)
    {
        $comment = ($comment) ?: $this->_helper()->__('Payment Processed.');
        $order = ($order) ?: $this->_getOrder();
        
        $status = self::ORDER_STATUS_PROCESSED;
        
        // State Validation
        if(!$this->_orderCanBeProcessed($order)) {
            // Error
            $msg = $this->_helper()->__(
                'The status of the order "%s" can not be set to "%s".',
                $order->getIncrementId(), self::ORDER_STATUS_PROCESSED
            );  
            Mage::throwException($msg);
            return $this;
        }
        
        // Unhold
        if($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
             // Unhold
            $order->unhold()->save();
        }
        
        if($isCustomerNotified = (bool)$this->getConfigData('send_mail_on_process')) {
            // $order->sendOrderUpdateEmail(true, $comment);
            $order->sendNewOrderEmail();
        }
                
        $order->setState(
            Mage_Sales_Model_Order::STATE_PROCESSING, 
            $status,
            $comment,
            $isCustomerNotified
        );
        
        $order->save();
        
//        Mage::dispatchEvent('nps_set_order_to_payment_processed', array('order' => $order));
        
        return $this;
    }
    
    protected function _setOrderToPaymentCanceled($comment = null,  Mage_Sales_Model_Order $order = null)
    {
        $comment = ($comment) ?: $this->_helper()->__('Payment Canceled.');
        $order   = ($order) ?: $this->_getOrder();
        
        $status = self::ORDER_STATUS_CANCELED;
        
        // State Validation
        if(!$this->_orderCanBeCanceled($order)) {
            // Error
            $msg = $this->_helper()->__(
                'The status of the order "%s" can not be set to "%s".',
                $order->getIncrementId(), self::ORDER_STATUS_CANCELED
            );                
             Mage::throwException($msg);
            return $this;
        }
        
        // Unhold
        if($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED) {
             // Unhold
            $order->unhold()->save();
        }
        
        // Send Email
        if($isCustomerNotified = (bool)$this->getConfigData('send_mail_on_cancel')) {
            $order->sendOrderUpdateEmail(true, $comment);
        }                
        
        $order->addStatusHistoryComment($comment, $status)
            ->save();
        
        $order->cancel()
            ->setStatus($status)
            ->save();
        
//        Mage::dispatchEvent('nps_set_order_to_payment_canceled', array('order' => $order));
        
        return $this;
        
    }
    
    protected function _setOrderInvoice($data)
    {
        $status     = ($this->getConfigData('invoice_status')) ?: null;
        $message    = ($this->getConfigData('invoice_message')) ?: null;
        
        $this->_getOrderService()
            ->orderInvoce($this->_getOrder(), $status, $message);
        
        return $this;
    }
    
    protected function _setOrderToHoldAvs($comment = null,  Mage_Sales_Model_Order $order = null)
    {
        $comment    = ($comment) ?: $this->_helper()->__('AVS Review');
        $order      = ($order) ?: $this->_getOrder();
        $status     = self::ORDER_STATUS_HOLDED_AVS;
        
        $this->_getOrderService()->orderHold($order, $status, $comment);
        
        return $this;
    }
    
    protected function _setOrderToHoldFraud($comment = null,  Mage_Sales_Model_Order $order = null)
    {
        $comment    = ($comment) ?: $this->_helper()->__('Fraud Review');
        $order      = ($order) ?: $this->_getOrder();
        $status     = self::ORDER_STATUS_HOLDED_FRAUD;
        
        $this->_getOrderService()->orderHold($order, $status, $comment);
        
        return $this;
    }


    /**************************************************************************/
    /************************* SOAP REQUEST DATA PREPARE - PROTECTED METHODS **/
    /**************************************************************************/
    
    /**
     * Returns an array with the data needed to perform a PayOnline3pRequest
     * 
     * @return array
     */
    protected function _getPayOnline3pRequestData()
    {
        $info = $this->getInfoInstance();
        
        $data = array(
            // Mandatory
            "psp_MerchantId"          => $this->_getPspMerchantId(),
            "psp_MerchTxRef"          => $this->_getPspMerchTxRef(),
            "psp_MerchOrderId"        => $this->_getPspMerchOrderId(),
            "psp_Amount"              => $this->_getPspAmount(),
            "psp_CustomerMail"        => $this->_getPspCustomerEmail(),
            "psp_PosDateTime"         => $this->_getPspPosDateTime(),
            "psp_MerchantMail"        => $this->getConfigData('merchcant_email'),
            // Payment Data
            "psp_Product"             => $info->getData('hd_bccp_gateway_cc_code'),
            "psp_NumPayments"         => $info->getData('hd_bccp_payments'),
            // URLs
            "psp_ReturnURL"           => $this->_getReturnUrl(),
            "psp_FrmBackButtonURL"    => $this->_getDeclinelUrl(),
            "psp_Version"             => $this->_getPspVersion(),
            "psp_FrmLanguage"         => $this->_getPspFrmLanguage(),
            "psp_Country"             => $this->_getPspCountry(),
            "psp_Currency"            => $this->_getPspCurrency(),
            "psp_TxSource"            => "WEB",
        );
        
        // Promo Code
        if ($promoCode = $info->getData('hd_bccp_gateway_promo_code')) {
            $data['psp_PromotionCode']  = $promoCode;
        }
        
        // Append Special Optional Data
        $this->_appendPayOnline3pRequestData($data);
        
        // Hash
        $data["psp_SecureHash"] = $this->_getPspSecureHash($data);
        
        // Sort
        ksort($data);
        
        return $data;
    }
    
    /**
     * @param array $data
     * @return \Hd_Nps_Model_Psp
     */
    protected function _appendPayOnline3pRequestData(&$data)
    {
        $this->_appendPspPlan($data)
            ->_appendAvsVisa($data)             // Visa Address Validation
            ->_appendAvsAmex($data)             // Amex Address Validation
            ->_appendAvsMaster($data)           // Mastercard Address Validation
            ->_appendFraudEvaluation($data);    // Fraud Evaluation Data
        
        // Debug Log 
        $this->dLog('FINAL PSP DATA');
        $this->dLog($data);
        return $this;
    }
    
    /**
     * 
     * Implements pspPlan Exceptions for:
     *  PlanZ (Naranja)
     *  Nevaplan (Nevada)
     * 
     * @todo Add Other Countries Exceptions
     * @param $data
     * @return \Hd_Nps_Model_Psp
     */
    protected function _appendPspPlan(&$data)
    {
        if($data['psp_Product'] == '9990') {
            
            $data['psp_Product'] = '9';
            $data['psp_Plan'] = 'PlanZ';
            $data['psp_NumPayments'] = '1';
            
        } else if ($data['psp_Product'] == '9991') {
            
            $data['psp_Product'] = '21';
            $data['psp_Plan'] = 'Nevaplan';
            $data['psp_NumPayments'] = '1';
            
        }
        return $this;
    }
    
    protected function _appendAvsVisa(&$data)
    {
        if($this->_isAvsVisaActive() && !$this->getConfigData('avs_visa_use_nps_form')) {
            // Append Data of Child Form
        }
        return $this;
    }
    
    protected function _appendAvsAmex(&$data)
    {
        if($this->_isAvsAmexActive()) {
            // Append Data of Child Form
        }
        return $this;
    }
    
    protected function _appendAvsMaster(&$data)
    {
        if($this->_isAvsMasterActive()) {
            // Append Data of Child Form
        }
        return $this;
    }
    
    protected function _appendFraudEvaluation(&$data)
    {
        if($this->_isFraudEvaluationActive()) {
            // Data Append
            try {
                
                $fraudDada = $this->getFraudProcessor()->getFraudPspData();
                $data = array_merge($data,$fraudDada);
                
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return $this;
    }
    
    /**************************************************************************/
    /************************* SOAP REQUEST DATA GETTERS - PROTECTED METHODS **/
    /**************************************************************************/

    /**
     * Returns Merchant Id from Bccp Promo or Config
     */
    protected function _getPspMerchantId()
    {
        $merchantId = $this->getInfoInstance()
            ->getData('hd_bccp_gateway_merchant_code');
        
        return ($merchantId) ? $merchantId 
            : $this->getConfigData('ws_merchant_id');
    }
    
    /**
     * Returns Merchant Secret Key
     * @return type
     */
    protected function _getPspSecretKey()
    {
        return $this->getConfigData('ws_merchant_secret_key');
    }
    
    /**
     * Returns Formated Amount
     * @return int
     */
    protected function _getPspAmount()
    {
        return round($this->_getAmount(),2) * 100;
    }
    
    /**
     * Returns Order Increment Id
     */
    protected function _getPspMerchOrderId()
    {
        if(Mage::getIsDeveloperMode()) {
            return  $this->_getReservedOrderId();
        }
        return $this->_getReservedOrderId();
    }
    
    /**
     * Hash UNICO @ por Orden/Merchant/Store
     * @return type
     */
    protected function _getPspMerchTxRef()
    {
        if(Mage::getIsDeveloperMode()) {
            return  substr($this->_getReservedOrderId() . "_" . md5( microtime() . mt_rand() ), 0, 39);
        }
        return  substr($this->_getReservedOrderId() . "_" . md5($this->getMerchantCode() .  Mage::app()->getStore()->getId()), 0, 39);
    }
    
    /**
     * Customer Email
     * @return type
     */
    protected function _getPspCustomerEmail()
    {
        $obj = $this->_isPlaceOrder() ? $this->getInfoInstance()->getOrder()
            : $this->getInfoInstance()->getQuote();
        return $obj->getCustomerEmail();
    }
    
    /**
     *  Secure Hash Implementation
     *
     * @param array $data
     * @return string
     */
    protected function _getPspSecureHash($data)
    {
        $string = "";
        if (is_array($data) && count($data) > 0 ) {
            ksort($data);
            foreach ($data as $field) {
                if(is_array($field)) {
                    continue;
                }
                $string .= $field;
            }
            $string .= $this->getConfigData('ws_merchant_secret_key');
        }
        return md5($string);
    }
    
    /**
     * Default Version
     */
    protected function _getPspVersion()
    {
        return '2.2';
    }
    
    /**
     * @todo REVISAR
     * @return type
     */
    protected function _getPspPosDateTime()
    {
        return date("Y-m-d H:i:s");
    }
    
    /**
     * Map By Store Country
     */
    protected function _getPspFrmLanguage()
    {
        $code = $this->_getCountryCode();
        return @$this->_countryLocale[$code];
    }
    
    /**
     * Map From Store Country
     */
    protected function _getPspCountry()
    {
        $code = $this->_getCountryCode();
        return @$this->_pspCountry[$code];
    }
    
    /**
     * Map From Store Base Currency
     */
    protected function _getPspCurrency()
    {
        $code = $this->_getCurrencyCode();
        return @$this->_pspCurrency[$code];
    }
    
    /**************************************************************************/
    /***************************** SIMPLE VALUES - PROTECTED GETTERS METHODS **/
    /**************************************************************************/
    
    /**
     * Return URL
     * @return string
     */
    protected function _getReturnUrl()
    {
        return $this->_getActionUrl('return');
    }
    
    /**
     * Decline Payment URL
     * @return string
     */
    protected  function _getDeclinelUrl()
    {
        return $this->_getActionUrl('decline');
    }

    /**
     * Return Url By Action
     * @return string
     */
    protected  function _getActionUrl($action = '')
    {
        if((bool)$this->getConfigData('iframe_active') && $this->_isSecure()) {
            return Mage::getUrl("nps/pay/{$action}", array('_secure'=> true));
        }
        return Mage::getUrl("nps/redirect/{$action}", array('_secure' => $this->_isSecure()));
    }
    
    /**
     * Order increment ID getter (either real from order or a reserved from quote)
     * @return string
     */
    protected function _getReservedOrderId()
    {
        $info = $this->getInfoInstance();
        if ($this->_isPlaceOrder()) {
            return $info->getOrder()->getIncrementId();
        } else {
            if (!$info->getQuote()->_getReservedOrderId()) {
                $info->getQuote()->reserveOrderId();
            }
            return $info->getQuote()->_getReservedOrderId();
        }
    }
    
    /**
     * @return string
     */
    protected function _getAmount()
    {
        $info = $this->getInfoInstance();
        return ($this->_isPlaceOrder())
            ? (double)$info->getOrder()->getQuoteBaseGrandTotal()
            : (double)$info->getQuote()->getBaseGrandTotal();
    }

    /**
     * Currency code getter
     * @return string
     */
    protected function _getCurrencyCode()
    {
        try {
            $info = $this->getInfoInstance();
            return ($this->_isPlaceOrder())
                ? $info->getOrder()->getBaseCurrencyCode()
                : $info->getQuote()->getBaseCurrencyCode();
        } catch (Exception $e) {
            $storeId = Mage::app()->getStore()->getId();
        }
        return Mage::getStoreConfig('currency/options/base', $storeId);
    }
    
    /**
     * Country code getter
     * @return string
     */
    protected function _getCountryCode()
    {
        try {
            $info = $this->getInfoInstance();
            $storeId  = ($this->_isPlaceOrder())
                ? $info->getOrder()->getStoreId()
                : $info->getQuote()->getStoreId();
        } catch (Exception $e) {
            $storeId = Mage::app()->getStore()->getId();
        }
        return Mage::getStoreConfig('general/country/default', $storeId);
    }
    
    /**************************************************************************/
    /*********************************** OBJECTS - PROTECTED GETTERS METHODS **/
    /**************************************************************************/

    /**
     * @return Hd_Nps_Model_Soap_Client
     */
    protected function _getSoapClient()
    {
        if (!$this->_soapClient) {
            $this->_soapClient = Mage::getModel('hd_nps/soap_client', array('method' => $this));
        }
        return $this->_soapClient;
    }
    
    /**
     * @return Hd_Bccp_Model_Sales_Order_Service
     */
    protected function _getOrderService()
    {
        return Mage::getSingleton('hd_bccp/sales_order_service');
    }
    
    /**
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        if (!$this->_isPlaceOrder()) {
            return null;
        }
        return $this->getInfoInstance()->getOrder();
    }
    
    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCheckoutSession()->getQuote();        
    }
    
    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    protected function _helper($key = 'data')
    {
        return Mage::helper("hd_nps/{$key}");
    }
    
    /**************************************************************************/
    /************************************ CONFIG - PROTECTED GETTERS METHODS **/
    /**************************************************************************/
    
    protected function _isSecure()
    {
        return Mage::app()->getStore()->isCurrentlySecure();
    }
    
//    protected function _
    
    protected function _isAvsVisaActive()
    {
        return (bool)$this->getConfigData('avs_visa_active');
    }
    
    protected function _isAvsAmexActive()
    {
        return (bool)$this->getConfigData('avs_amex_active');
    }
    
    protected function _isAvsMasterActive()
    {
        return (bool)$this->getConfigData('avs_master_active');
    }
    
    protected function _isFraudEvaluationActive()
    {
        return (bool)$this->getConfigData('fraud_active');
    }
    
    /**
     * @return boolean
     */
    protected function _isPlaceOrder()
    {
        $info = $this->getInfoInstance();
        if ($info instanceof Mage_Sales_Model_Quote_Payment) {
            return false;
        } elseif ($info instanceof Mage_Sales_Model_Order_Payment) {
            return true;
        }
    }
    
    /**
     * Filters $data array with the $ref array
     * 
     * @param array $data
     * @param array $ref
     */
    protected function _keyFilter($data, $ref)
    {
        return array_intersect_key($data, array_flip($ref));
    }
    
    /**************************************************************************/
    /*************************************** CONFIG - PUBLIC GETTERS METHODS **/
    /**************************************************************************/
    
    /**************************************************************************/
    /** @TODO FROM HERE -> MOVE TO EXTERNAL COFIG MODEL ***********************/
    /**************************************************************************/
    
    /**
     * @todo Decouple In Other Model Hd_Nps_Psp_Config
     */
    public function getConfig()
    {
        
    }
    
    public function getPspCountries()
    {
        return $this->_pspCountry;
    }
    
    public function getPspCurrencies()
    {
        return $this->_pspCurrency;
    }
    
    public function getPspProducts()
    {
        return $this->_pspProduct;
    }
    
    public function getPspProductsLabel()
    {
        return $this->_pspProductType;
    }
    
    public function getCountryCurrencies($country)
    {
        return (isset($this->_countryCurrency[$country]))
            ? $this->_countryCurrency[$country]
            : null;
    }
    
    public function getCountryLocale($country)
    {
        return @$this->_countryLocale[$country];
    }
    
    public function getCountryProduct($country, $product)
    {
        $allCc = $this->_pspProduct[$product];
        return $this->_keyFilter($allCc, $this->_countryProduct[$country][$product]);
    }
    
    public function getCountryProductTypes($country)
    {
        if(!isset($this->_countryProduct[$country])) {
            return null;
        }
        return $this->_keyFilter($this->_pspProductType, array_keys($this->_countryProduct[$country]));
    }
    
    public function getProductCountries($product)
    {
        $countries = array();
        foreach($this->getPspCountries() as $country => $name) {
            if($this->countryHasProduct($country, $product)) {
                $countries[] = $country;
            }
        }
        return $countries;
    }
    
    public function countryHasCurrency($country, $currency)
    {
        $currencies = $this->getCountryCurrencies($country);
        return ($currencies) ? in_array($currency, $currencies) : false;
    }
    
    public function countryHasProduct($country, $product)
    {
        $products = $this->getCountryProductTypes($country);
        return ($products) ? array_key_exists($product, $products) : false;
    }
  
    protected $_countryProduct = array(
        'AR' => array(
            'cc' => array(
                // Creditcards
                1,2,5,8,9,10,14,17,20,21,42,43,48,49,50,61,63,65,72,95,110,
                // Discount Cards
                45,46,47,52,58,51
            ),
            'bcc' => array(
                // Creditcards
                1,2,5,8,9,10,14,17,20,21,42,43,48,49,50,61,63,65,72,95,110,
                // Discount Cards
                45,46,47,52,58,51
            ),
            'bank' => array(
                320
            ),
            'cash' => array(
                300,301,302
            ),
            'rcc' => array(
                // Remote DineroMail (14)
                1,2,5,8,14,42
            ),
            'rcash' => array(
                // Remote DineroMail (14)
                300,301,302,303,304
            ),
        ),
        'CL' => array(
            'rcc' => array(
                // Remote Transbank (8) / DineroMail (14)
                1,2,5,14,103,107
            ),
            'rcash' => array(
                // Remote DineroMail (14)
                305,313
            ),
        ),
        'PE' => array(
            'cc' => array(
                // Creditcards
                1,5,14
            ),
            'rcc' => array(
                // Remote Visa Peru (9) / MC Peru (10)
                5,14
            ),
            'rbank' => array(
                // Bank
                321
            ),
        ),
        'BR' => array(
            'cc' => array(
                // Creditcards
                1,2,4,5,14,101,102,104,105
            ),
            'rcc' => array(
                // Remote Cielo (11)/ Dineromail (14)
                1,2,4,5,14,101,102,104,105
            ),
            'rbank' => array(
                // Bank
                321
            ),
            'rcash' => array(
                // Remote DineroMail (14)
                306
            ),
        ),
        'CO' => array(
            'cc' => array(
                // Creditcards
                1,2,5,14,106
            ),
            'rcc' => array(
                // Remote Dineromail (14)
                1,2,5,14,106
            ),
            'rbank' => array(
                // Bank
                321
            ),
        ),
        'PY' => array(
            'cc' => array(
                // Creditcards
                1,2,5,14,101
            ),
        ),
        'VE' => array(
            'cc' => array(
                // Creditcards
                1,5,14
            ),
        ),
        'MX' => array(
            'cc' => array(
                // Creditcards
                1,5,14
            ),
            'rcc' => array(
                // Remote Dineromail (14) (Only 'MXN')
                1,5,14
            ),
            'rbank' => array(
                // Remote Bank
                321
            ),
            'rcash' => array(
                // Remote DineroMail (14)
                307,308,309,310,311,312
            ),
        ),
        'UY' => array(
            'cc' => array(
                // Creditcards
                1,2,5,17,101
            ),
        ),
        'US' => array(
            'cc' => array(
                // Creditcards
                1
            ),
            'rbank' => array(
                // Bank
                321
            ),
        ),
        'AT' => array(
            'rbank' => array(
                // Bank
                321
            ),
        ),
        'CA' => array(
            'rbank' => array(
                // Bank
                321
            ),
        ),
        'CR' => array(
            'rbank' => array(
                // Bank
                321
            ),
        ),
        'DE' => array(
            'rbank' => array(
                // Bank
                321
            ),
        ),
        'ES' => array(
            'rbank' => array(
                // Bank
                321
            ),
        ),
    );
    
    protected $_countryLocale = array(
        'AR' => 'es_AR',
        'CL' => 'es_ES',
        'PE' => 'es_ES',
        'BR' => 'pt_BR',
        'CO' => 'es_ES',
        'PY' => 'es_ES',
        'VE' => 'es_ES',
        'MX' => 'es_ES',
        'UY' => 'es_AR',
        'US' => 'en_US',
        'CA' => 'en_US',
        'AT' => 'de_DE',
        'CR' => 'es_ES',
        'DE' => 'de_DE',
        'ES' => 'es_ES',
    );
    
    protected $_countryCurrency = array (
        'AR' => array('ARS'),
        'CL' => array('CLP','USD'),
        'PE' => array('PEN'),
        'BR' => array('BRL'),
        'CO' => array('COP'),
        'PY' => array('PYG'),
        'VE' => array('VEF'),
        'MX' => array('MXN','USD'),
        'UY' => array('UYU','USD'),
        'US' => array('USD'),
        'CA' => array('CAD'),       // Canada       (BankPayment_3p)
        'AT' => array('EUR','USD'), // Austria      (BankPayment_3p)
        'CR' => array('CRC','USD'), // Costa Rica   (BankPayment_3p)
        'DE' => array('EUR','USD'), // Alemania     (BankPayment_3p)
        'ES' => array('EUR','USD'), // España       (BankPayment_3p)
    );
    
    /**
     * Mapping Matrix ISO / PSP
     * @var array
     */
    protected $_pspCurrency = array(
        'ARS' => '032', // Peso Argentino
        'CLP' => '152', // Peso Chileno 
        'USD' => '840', // Dólar estadounidense 
        'PEN' => '604', // Nuevo Sol Peruano 
        'BRL' => '986', // Real Brasileño
        'COP' => '170', // Peso Colombiano 
        'PYG' => '600', // Guarani 
        'VEF' => '937', // Bolivar Fuerte Venezolano 
        'MXN' => '484', // Peso Mexicano 
        'UYU' => '858', // Peso Uruguayo        
        'CAD' => '124', // Dólares Canadienses 
        'CRC' => '188', // Colón Costarricense 
        'EUR' => '978', // Euro
    );
    
    /**
     * Mapping Matrix ISO / PSP
     * @var array
     */
    protected $_pspCountry = array(
        'AR' => 'ARG',
        'CL' => 'CHL',
        'PE' => 'PER',
        'BR' => 'BRA',
        'CO' => 'COL',
        'PY' => 'PRY',
        'VE' => 'VEN',
        'MX' => 'MEX',
        'UY' => 'URY',
        'US' => 'USA',
        'CA' => 'CAN', // Canada        (BankPayment_3p)
        'AT' => 'AUT', // Austria       (BankPayment_3p)
        'CR' => 'CRI', // Costa Rica    (BankPayment_3p)
        'DE' => 'DEU', // Alemania      (BankPayment_3p)
        'ES' => 'ESP', // España        (BankPayment_3p)
    );
        
    protected $_pspProductType = array(
        'cc'    => 'Creditcard',
        'bcc'   => 'Bank Creditcard',
        'bank'  => 'Bank Payment',
        'cash'  => 'Cash Payment',
        'rcc'   => 'Remote Creditcard',
        'rcash' => 'Remote Cash Payment',
        'rbank' => 'Remote Bank Payment',
    );
    
    protected $_pspProduct = array(
        'cc' => array(
            1       => 'American Express',
            2       => 'Diners',
            4       => 'JCB',
            5       => 'Mastercard',
            8       => 'Cabal',
            9       => 'Naranja',
            10      => 'Kadicard',
            14      => 'Visa',
            17      => 'Lider',
            20      => 'Credimas',
            21      => 'Nevada',
            29      => 'Visa Naranja',
            42      => 'Shopping',
            43      => 'Italcred',
            45      => 'Club La Nación',
            46      => 'Club Personal',
            47      => 'Club Arnet',
            48      => 'Mas',
            49      => 'Naranja MO',
            50      => 'Pyme Nación',
            51      => 'Clarin 365',
            52      => 'Club Speedy',
            55      => 'Visa Débito',
            58      => 'Club La Voz',
            61      => 'Nexo',
            63      => 'Nativa',
            65      => 'Argencard',
            72      => 'Consumax',
            95      => 'Coopeplus',
            101     => 'Discover',
            102     => 'Elo',
            103     => 'Magna',
            104     => 'Aura',
            105     => 'Hipercard',
            106     => 'Credencial Col',            
            9990    => 'Naranja (Plan Z)',
            9991    => 'Nevada (Nevaplan)',
        ),
        'cash' => array(
            300     => 'AR Rapipago',
            301     => 'AR Pagofacil',
            302     => 'AR Bapropagos',
            303     => 'AR Ripsa',
            304     => 'AR Cobro Express',
            305     => 'CL ServiPag',
            306     => 'BR Boleto Itaú',
            307     => 'MX Seven Eleven',
            308     => 'MX Oxxo',
            309     => 'MX Bancomer',
            310     => 'MX Santander',
            311     => 'MX IXE',
            312     => 'MX Scotiabank',
        ),
        'bank' => array(
            320     => 'Pago Mis Cuentas',
            321     => 'Safety Pay',
        ),
        
    );
    
}