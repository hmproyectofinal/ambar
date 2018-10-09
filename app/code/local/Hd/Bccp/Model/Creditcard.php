<?php
//class Hd_Bccp_Model_Creditcard extends Mage_Core_Model_Abstract
class Hd_Bccp_Model_Creditcard extends Hd_Bccp_Model_Abstract
{
    protected $_eventPrefix = 'hd_bccp_creditcard';
    
    protected $_eventObject = 'creditcard';
    
    protected function _construct()
    {
        $this->_init("hd_bccp/creditcard", "creditcard_id");
    }
    
    public function addBank(Hd_Bccp_Model_Bank $bank)
    {
        // Seteo inicial
        if(!is_array($this->getData('banks'))) {
            $this->setData('banks', array());
        }
        $this->_data['banks'][$bank->getId()] = $bank;
        return $this;
    }
    
    /**
     * Load/Retrieve Payments
     * @return array
     */
    public function getPayments($countryId = null)
    {
        if(!is_array($this->getData('payments'))) {
            $paymentCollection = $this->_getPaymentCollectionModel();
            $paymentCollection->addCreditcardFilter($this->getCreditcardId());
            if ($countryId) {
                $paymentCollection->addCountryFilter($countryId);
            }
            $payments = array();
            foreach($paymentCollection as $payment) {
                $payments[] = $payment;
            }
            $this->setData('payments', $payments);
        }
        return $this->getData('payments');
    }
    
    /**
     * Levanta y Prepara 'grouped_payments' desde la db/session en: 
     * 
     * - Array: grouped_payments[country] = (array)payments
     * - Flat(para el Varien_Form): grouped_payments-{country_id} = (array)payments
     * 
     * @return \Hd_Bccp_Model_Creditcard
     */
    public function loadGroupedPayments()
    {
        if (!is_array($this->getData('grouped_payments'))) {
            $countryPayments = array();
            foreach($this->getPayments() as $payment) {
                $countryId = (is_null($payment['country_id'])) ? 'default' : $payment['country_id'];
                $countryPayments[$countryId][] = ($payment instanceof Hd_Bccp_Model_Creditcard_Payment)
                    ? $payment->getData()
                    : $payment;
            }
            $this->setData('grouped_payments', $countryPayments);
        }
        // Hay que armar los datos por id como le gusta al Varien_Form
        foreach ($this->getData('grouped_payments') as $countryId => $countryPayments) {
            $this->setData("grouped_payments-{$countryId}", $countryPayments);
        }
        return $this;
    }
    
    /**
     * Load/Retrieve Payment Method Codes
     * 
     * @return array
     */
    public function getMethodCodes()
    {
        if(!is_array($this->getData('method_codes'))) {
            $conn = $this->getResource()->getReadConnection();
            $select = $conn->select()
                ->from('hd_bccp_creditcard_mapping')
                ->where('creditcard_id = ?', $this->getCreditcardId());
            $methodCodes = $conn->fetchAll($select);
            $this->setData('method_codes', $methodCodes);
        }
        return $this->getData('method_codes');
    }
    
    /**
     * Levanta y Prepara 'grouped_method_codes' desde la db/session en: 
     * 
     * - Array: grouped_method_codes[method][country] = code
     * - Flat(para el Varien_Form): grouped_method_codes-{mehod}{country_id} = code
     * 
     * @return \Hd_Bccp_Model_Creditcard
     */
    public function loadGroupedMethodCodes()
    {
        // Si no se recupero de la session en el controller...
        if (!is_array($this->getData('grouped_method_codes'))) {
            $methodCodes = array();
            foreach($this->getMethodCodes() as $code) {
                $countryId      = (is_null($code['country_id'])) ? 'default' : $code['country_id'];
                $creditcardCode = (is_null($code['code'])) ? '-' : $code['code'];
                $methodCodes[$code['method']][$countryId] = $creditcardCode;
            }
            $this->setData('grouped_method_codes', $methodCodes);
        }
        // Hay que armar los datos por id como le gusta al Varien_Form
        foreach ($this->getData('grouped_method_codes') as $method => $countryCodes) {
            foreach ($countryCodes as $countryId => $creditcardCode) {
                $this->setData("grouped_method_codes-{$method}-{$countryId}", $creditcardCode);
            }
        }
        return $this;
    }
    
    /**
     * @return Hd_Bccp_Model_Resource_Creditcard_Payment_Collection
     */
    protected function _getPaymentCollectionModel()
    {
        return Mage::getResourceModel('hd_bccp/creditcard_payment_collection');
    }
    
    /**
     * @return Hd_Bccp_Model_Resource_Bank_Collection
     */
    protected function _getBankCollectionModel()
    {
        return Mage::getResourceModel('hd_bccp/bank_collection');
    }

    
    
}