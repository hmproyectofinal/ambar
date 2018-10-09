<?php
class Hd_Bccp_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_CONFIG_PATH_COUNTRY_SUPPORT_ENABLE = 'hd_bccp/country_support/enable';
    
    const XML_CONFIG_PATH_STORE_SUPPORT_ENABLE = 'hd_bccp/store_support/enable';
    
    const XML_CONFIG_PATH_PAYMENT_METHODS        = 'hd_bccp/methods';
    
    // Custom Payment Fields
    protected $_paymentCustomFields = array(
        'cc_id',                    // Cambiar x hd_bccp_cc_id
        'cc_name',                  // Cambiar x hd_bccp_cc_name
        'bank_name',                // Cambiar x hd_bccp_bank_name
        'bank_id',                  // Cambiar x hd_bccp_bank_id
        'payments',                 // Cambiar x hd_bccp_payments
        'cc_payment_id',            // Cambiar x hd_bccp_payment_id
        'gateway_cc_code',          // Cambiar x hd_bccp_gateway_cc_code
        'gateway_bank_code',        // Cambiar x hd_bccp_gateway_bank_code
        'gateway_merchant_code',    // Cambiar x hd_bccp_gateway_merchant_code
        'gateway_promo_code',       // Cambiar x hd_bccp_gateway_promo_code
    );
    
    protected $_paymentMethods;
    
    public function isCountrySupportEnable()
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_PATH_COUNTRY_SUPPORT_ENABLE);
    }
    
    public function isStoreSupportEnable()
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_PATH_STORE_SUPPORT_ENABLE);
    }
    
    public function getCreditcardName($id)
    {
        $options = Mage::getSingleton('hd_bccp/system_config_source_creditcard')->toOptionHash(false);
        foreach ($options as $k => $v) {
            if($k == $id) {
                return $v;
            }
        }
        return $id;
    }
    
    public function getCountryName($id)
    {
        $options = Mage::getSingleton('hd_bccp/system_config_source_country')->toOptionHash(false);
        foreach ($options as $k => $v) {
            if($k == $id) {
                return $v;
            }
        }
        return $id;
    }


    public function hasPaymentMethods()
    {
        return (bool)count($this->getPaymentMethods());
    }
    
    /**
     * Devuelve todos los metodos de pago compatibles con Hd_Bccp
     */
    public function getPaymentMethods()
    {
        if(!$this->_paymentMethods) {
            $paymentMethods = array();
            $methodConfig = Mage::getStoreConfig(self::XML_CONFIG_PATH_PAYMENT_METHODS);
            if($methodConfig) {
                foreach ($methodConfig as $methodData) {
                    $paymentMethods[$methodData['code']] = Mage::getModel($methodData['model']);
                }
            }
            $this->_paymentMethods = $paymentMethods;
        }
        return $this->_paymentMethods;
    }
    
    public function getBccPaymentMethods()
    {
        $result = array();
        foreach($this->getPaymentMethods() as $code => $model) {
            if($model->getPaymentSourceType() == 'bcc') {
                $result[$code] = $model;
            }
        }
        return $result;
    }
    
    public function getPaymentMethod($methodCode) 
    {
        foreach ($this->getPaymentMethods() as $code => $model) {
            if ($code == $methodCode) {
                return $model;
            }
        }
        return null;
    }

    public function getCreditcardCodeOptions($methodCode, $countryId = null)
    {
        $method  = $this->getPaymentMethod($methodCode);
        $options = array();
        if ($method && $method instanceof Hd_Bccp_Model_Payment_Method_Interface) {
            /* @var $method Hd_Bccp_Model_Payment_Method_Interface */
            $codes = $method->getCreditcardCodes($countryId);
            if ($codes && count($codes) > 0) {
                // Unselect Option
                $options[''] = $this->__('-- Please Select --');
                // Unavailable Option
                $options['-'] = $this->__('-- Unsopported Credit Card --');
                foreach ($codes as $k => $v) {
                    $options[$k] = $v;
                }
            } else {
                $options[''] = ($countryId) 
                    ? $this->__('-- Unsopported Country --')
                    : $this->__('-- Unsopported Credit Card --');
            }
        }
        return $options;
    }
    
    public function getBankCodeOptions($methodCode, $countryId = null)
    {
        $method  = $this->getPaymentMethod($methodCode);
        $options = array();
        if ($method && $method instanceof Hd_Bccp_Model_Payment_Method_Interface) {
            /* @var $method Hd_Bccp_Model_Payment_Method_Interface */
            $codes = $method->getBankCodes();
            if ($codes && count($codes) > 0) {
                // Unselect Option
                $options[''] = $this->__('-- Please Select --');
                // Unavailable Option
                $options['-'] = $this->__('-- Unsopported Bank --');
                foreach ($codes as $k => $v) {
                    $options[$k] = $v;
                }
            } else {
                $options[''] = $this->__('-- Unsopported Bank --');
            }
        }
        return $options;            
    }
    
    
}

