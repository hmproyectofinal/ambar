<?php
class Hd_Nps_Model_Psp_Cc extends Hd_Nps_Model_Psp
    implements Hd_Bccp_Model_Payment_Method_Interface
{
    /**************************************************************************/
    /************************************ Mage_Payment_Model_Method_Abstract **/
    /**************************************************************************/
    
    protected $_code = 'nps_cc';

    protected $_canOrder                    = true;
    protected $_canAuthorize                = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = false;

    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = false;
    protected $_canVoid                     = true;

    protected $_canUseCheckout              = true;
    protected $_canUseInternal              = false;
    protected $_canUseForMultishipping      = false;

    protected $_canFetchTransactionInfo     = true;
    protected $_isInitializeNeeded          = false;
    protected $_canReviewPayment            = false;
    protected $_canCreateBillingAgreement   = false;
    protected $_canManageRecurringProfiles  = false;
    
    /**************************************************************************/
    /******************************** Hd_Bccp_Model_Payment_Method_Interface **/
    /**************************************************************************/
    
    protected $_formBlockType = 'hd_nps/payment_form_cc';
    
    protected $_infoBlockType = 'hd_nps/payment_info_cc';
    
    protected $_paymentSource = 'hd_nps/payment_source_cc';
    
    protected $_paymentSourceType = 'cc';
    
    protected $_paymentSourceModel;
    
    protected $_xmlConfigPathSoapWsdlUrl = 'payment/nps_cc/ws_wsdl_url';
    
    public function getPaymentSource()
    {
        if(!$this->_paymentSourceModel) {
            $this->_paymentSourceModel = Mage::getModel($this->_paymentSource, array('payment_method' => $this));
        }
        return $this->_paymentSourceModel;
    }
    
    public function getPaymentSourceType()
    {
        return $this->_paymentSourceType;
    }
    
    public function getMerchantCode()
    {
        return $this->getConfigData('ws_merchant_id');
    }
    
    public function getBankCodes($countryId = null)
    {
        return false;
    }
    
    public function getCreditcardCodes($countryId = null)
    {
        // All Available CCs
        if(is_null($countryId)) {
            return $this->_pspProduct['cc'];
        }
        // Unavailable Country
        if(!$this->countryHasProduct($countryId, 'cc')) {
            return false;
        }
        // Country OK
        return $this->getCountryProduct($countryId, 'cc');
    }
    
    public function isAvailable($quote = null)
    {
        return ($this->getConfigData('ws_wsdl_url')
                && $this->getConfigData('ws_merchant_id')
                && $this->getConfigData('ws_merchant_secret_key')
                && $this->getConfigData('merchcant_email'))
            ? parent::isAvailable($quote)
            : false;
    }
    
    
}