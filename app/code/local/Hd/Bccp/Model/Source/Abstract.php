<?php
abstract class Hd_Bccp_Model_Source_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Payment Method Model
     */
    protected $_methodModel;
    
    /**
     * Payments Form Helper Class Path
     */
    protected $_formHelperClass = 'hd_bccp/form';
    
    
    protected $_isBackendCheckout;
    
    /********************************************************* PUBLIC METHODS */
    
    public function __construct()
    {
        parent::__construct();
        @$params = func_get_arg(0);
        if(!isset($params['payment_method'])) {
            Mage::throwException($this->_helper()->__('Hd_Bccp Implementation Error: Payment Method not present in construct.'));
        }
        $methodModel = $params['payment_method'];
        if(!$methodModel instanceof Hd_Bccp_Model_Payment_Method_Interface) {
            Mage::throwException($this->_helper()->__('Hd_Bccp Implementation Error: The Payment Method "%s", is not supported', $methodModel->getTitle()));
        }
        $this->_methodModel = $params['payment_method'];
    }
    
    public function hasPaymentOptions()
    {
        $collection = $this->getAvailableCreditcards();
//        if(Mage::getIsDeveloperMode()) {
//            Mage::log(__METHOD__ . ' | IS LOADED:' . (int)$collection->isLoaded() . ' | COUNT:' . (int)$collection->count() );            
//        }
        return (bool)$collection->count();
    }
    
    /**
     * 
     * Total del Quote
     * - En el caso que tenga "collectado" el "hd_bccp_surcharge", lo resta -
     * 
     * @return float 
     */
    public function getQuoteTotal()
    {
        $totals = $this->getQuote()->collectTotals()->getTotals();
        // En el caso que vuelva atras y el metodo este guardado...
        
        $diff = ($surcharge = @$totals['hd_bccp_surcharge'])
            ? $surcharge->getValue() : 0;
        
        $total = $totals['grand_total']->getValue() - $diff;
        return $total;
    }
    
    /**
     * Devuelve el Metodo de Pago
     * 
     * @return Hd_Bccp_Model_Payment_Method_Interface | Mage_Payment_Model_Method_Abstract
     */
    public function getMethod()
    {
        if (!$this->_methodModel) {
            Mage::throwException('Payment Method  is not defined.');
        }
        return $this->_methodModel;
    }
    
    /**
     * Devuelve el Codigo del Metodo de Pago
     * @return type
     */
    public function getMethodCode()
    {
        return $this->getMethod()->getCode();
    }
    
    /**
     * Devuelve el Helper de Form
     *  
     * @return Hd_Bccp_Helper_Form
     */
    public function getFormHelper()
    {
        return Mage::helper($this->_formHelperClass);
    }
    
    /**
     * 
     * @param type $id
     * @param type $countryId
     * @param type $method
     * @return Hd_Bccp_Model_Creditcard
     */
    public function getCreditcard($id, $countryId = null, $method = null)
    {
        return $this->_loadModel($this->_getCreditcardModel(), $id, $countryId, $method);
    }
    
    /**
     * 
     * @param type $id
     * @param type $countryId
     * @param type $method
     * @return Hd_Bccp_Model_Bank
     */
    public function getBank($id, $countryId = null, $method = null)
    {
        return $this->_loadModel($this->_getBankModel(), $id, $countryId, $method);
    }
    
    /**
     * 
     * @param type $id
     * @param type $countryId
     * @param type $method
     * @return Hd_Bccp_Model_Creditcard_Payment
     */
    public function getPayment($id, $countryId = null, $method = null)
    {
        return $this->_loadModel($this->_getPaymentModel(), $id, $countryId, $method);
    }
    
    protected function _loadModel($model, $id, $countryId = null, $method = null, $storeId = null)
    {
        
        $method = ($method) ? $method : $this->getMethodCode();
        
        $countryId = ($this->_helper()->isCountrySupportEnable())
            ? ($countryId) ? $countryId : $this->_getCountryId()
            : null;
        
        $storeId = ($this->_helper()->isStoreSupportEnable())
            ? ($storeId) ? $storeId : $this->_getStoreId()
            : null;
        
        $model->setMethodCode($method)
            ->setCountryId($countryId)
            ->setStoreId($storeId);
        
        $model->load($id);
        
        return $model;
    }
    
    /**
     * Devuelve el Valor de cada cuota
     * 
     * @param type $total
     * @param type $coef
     * @param type $payments
     * @return type
     */
    protected function _getPaymentsAmount($total, $coef, $payments)
    {
        return ((float)$total * (float)$coef)/$payments;
    }
    
    /**
     *  Calcula y devuelve el Valor de Recargo o Interes
     * 
     * @param float $total
     * @param float $coef
     * @return float
     */
    protected function _calculateSurcharge($total, $coef)
    {
        if ($total == 0) {
            return 0;
        }
        $amount = ((float)$total * (float)$coef) - $total;
        return $amount;
    }
    
    protected function _getCountryId()
    {
        $storeId    = $this->_getStoreId();
        $countryId  = Mage::getStoreConfig('general/country/default', $storeId);
//        if(Mage::getIsDeveloperMode()) {
//            Mage::log(__METHOD__ . " Store: {$storeId} Country: {$countryId}");
//        }
        return $countryId;
    }
    
    protected function _getStoreId()
    {
        if($this->isBackendCheckout()) {
            return $this->getQuote()->getStoreId();
        }
        // Support Request Outside Checkout (Only on Fronted)
        return Mage::app()->getStore()->getId();
    }
    
    /**
     * @param string $key
     * @return Hd_Bccp_Helper_Data
     */
    protected function _helper($key = null)
    {
        return ($key) ? Mage::helper("hd_bccp/$key")
            : Mage::helper("hd_bccp");
    }
    
    /**
     * @return Hd_Bccp_Model_Bank
     */
    protected function _getBankModel()
    {
        return Mage::getModel('hd_bccp/bank');
    }

    /**
     * @return Hd_Bccp_Model_Resource_Bank_Collection
     */
    protected function _getBankCollection()
    {
        return $this->_getBankModel()->getCollection();
    }

    /**
     * @return Hd_Bccp_Model_Creditcard
     */
    protected function _getCreditcardModel()
    {
        return Mage::getModel('hd_bccp/creditcard');
    }

    /**
     * @return Hd_Bccp_Model_Resource_Creditcard_Collection
     */
    protected function _getCreditcardCollection()
    {
        return $this->_getCreditcardModel()->getCollection();
    }

    /**
     * @return Hd_Bccp_Model_Creditcard_Payment
     */
    protected function _getPaymentModel()
    {
        return Mage::getModel('hd_bccp/creditcard_payment');
    }

    /**
     * @return Hd_Bccp_Model_Resource_Creditcard_Payment_Collection
     */
    protected function _getPaymentCollection()
    {
        return $this->_getPaymentModel()->getCollection();
    }

    /**
     * @return Mage_Payment_Model_Method_Abstract
     */
    protected function _getMethodInstance()
    {
        return $this->getQuote()->getPayment()->getMethodInstance();
    }
    
    /**
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomer()
    {
       return $this->_getCustomerSession()->getCustomer();
    }
    
    /**
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
       return Mage::getSingleton('customer/session');
    }
    
    /**
     * @return Mage_Sales_Model_Quote
     */    
    public function getQuote()
    {
        return ($this->_isBackendCheckout) 
            ? $this->_getAdminhtmlSessionQuote()->getQuote()
            : $this->_getCheckoutSession()->getQuote();
    }
    
    public function isBackendCheckout()
    {
        return $this->_isBackendCheckout;
    }
    
    public function setIsBackendCheckout($value)
    {
        $this->_isBackendCheckout = $value;
        return $this;
    }
    
    protected function _getAdminhtmlSessionQuote()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }
    
    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession() 
    {
        return Mage::getSingleton('checkout/session');
    }
    
    
    /**************************************************************************/
    /******************************************************* ABSTRACT METHODS */
    /**************************************************************************/
    
    /**
     * Metodo para preparar los campos recibidos del metodo de pago
     * 
     * @abstract 
     */
    abstract public function praparePaymentImportData(Varien_Object $data, Mage_Sales_Model_Quote_Payment $payment);
    
    /**
     * Metodo para calcular el recargo
     * 
     * @abstract 
     */
    abstract public function getSurchargeAmount($total, Mage_Sales_Model_Quote_Payment $payment);
    
    /**
     * 
     * @abstract 
     */
    abstract public function getAvailableCreditcards();
    
}