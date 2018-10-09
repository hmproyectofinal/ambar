<?php

class Hd_Bccp_Adminhtml_Bccp_CreditcardController
    extends Hd_Bccp_Controller_Adminhtml_Bccp
{
    
    protected $_gridBlock           = 'hd_bccp/adminhtml_bccp_creditcard';
    
    protected $_editContentBlock    = 'hd_bccp/adminhtml_bccp_creditcard_edit';
    
    protected $_editLeftBlock       = 'hd_bccp/adminhtml_bccp_creditcard_edit_tabs';
    
    protected $_modelClass          = 'hd_bccp/creditcard';
    
    protected $_entityName          = 'Credit Card';
    
    protected $_modelNamespace      = Hd_Bccp_Block_Adminhtml_Bccp_Creditcard::REGISTRY_MODEL_NAMESPACE;
    
    protected function _initAction()
    {
        // Load Layouts
        parent::_initAction();
        // Subtitle
        $this->_title($this->_helper()->__('Manage Credit Cards'));
        // Menu
        $this->_setActiveMenu('system/hd_bccp/creditcard');
        
        return $this;
    }
    
    /**
     * Validacion de Parametros
     * 
     * @param type $params
     * @return type
     */
    protected function _prepareParams($params)
    {
        // Prepare Country
        $countries = array('default');
        if (Mage::helper('hd_bccp')->isCountrySupportEnable() && isset($params['country_ids'])) {
            $countries = array_merge($countries, $params['country_ids']);
        }
        // Prepare Payments
        if(isset($params['grouped_payments'])) {
            $payments = array();
            foreach ($params['grouped_payments'] as $countryId => $countryPayments) {
                // Caso en que se elimine un pasi pero vengan los payments asociados
                if (!in_array($countryId, $countries)) {
                    continue;
                }
                $countryId = ($countryId == 'default') ? null : $countryId;
                foreach ($countryPayments as $payment) {
                    $payment = array_filter($payment);
                    // Chau a los "flagueados" como "delete" sin "payment_id"
                    if(isset($payment['delete']) && !isset($payment['payment_id'])) {
                        continue;
                    }
                    $payments[] = $payment;
                }
            }
            $params["payments"] = $payments;
            unset($params['grouped_payments']);
        }
        // Prepare Method Codes
        if(isset($params['grouped_method_codes'])) {
            $methods = array();
            foreach ($params['grouped_method_codes'] as $methodCode => $countryCodes) {
                foreach ($countryCodes as $countryId => $creditcardCode) {
                    // Validacion de Country
                    if (in_array($countryId, $countries)) {
                        $countryId      = ($countryId == 'default') ? null : $countryId;
                        $creditcardCode = ($creditcardCode == '-') ? null : $creditcardCode;
                        $methods[] = array(
                            'creditcard_id' => $params['id'],
                            'country_id'    => $countryId,
                            'code'          => $creditcardCode,
                            'method'        => $methodCode,
                        );
                    }
                }
            }
            $params["method_codes"] = $methods;
            unset($params['grouped_method_codes']);
        }
        return parent::_prepareParams($params);
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/hd_bccp/creditcard');
    }
    
}