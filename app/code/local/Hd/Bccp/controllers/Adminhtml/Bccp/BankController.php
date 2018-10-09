<?php

class Hd_Bccp_Adminhtml_Bccp_BankController
    extends Hd_Bccp_Controller_Adminhtml_Bccp
{
    
    protected $_gridBlock           = 'hd_bccp/adminhtml_bccp_bank';
    
    protected $_editContentBlock    = 'hd_bccp/adminhtml_bccp_bank_edit';
    
    protected $_editLeftBlock       = 'hd_bccp/adminhtml_bccp_bank_edit_tabs';
    
    protected $_modelClass          = 'hd_bccp/bank';
    
    protected $_entityName          = 'Bank';

    protected function _initAction()
    {
        // Load Layouts
        parent::_initAction();
        // Subtitle
        $this->_title($this->_helper()->__('Manage Banks'));
        // Menu
        $this->_setActiveMenu('system/hd_bccp/bank');
        
        return $this;
    }
    
    protected function _getModelNamespace()
    {
        return Hd_Bccp_Block_Adminhtml_Bccp_Bank::REGISTRY_MODEL_NAMESPACE;
    }
    
    /**
     * Validacion de Parametros
     * 
     * @param type $params
     * @return type
     */
    protected function _prepareParams($params)
    {
        // Country ID
        if (isset($params['country_id']) && $params['country_id'] == '*') {
            $params['country_id'] = null;
        }
        // Prepare Method Codes
        if(isset($params['grouped_method_codes'])) {
            $methods = array();
            foreach ($params['grouped_method_codes'] as $method => $code) {
                $code = ($code == '-') ? null : $code;
                $methods[] = array(
                    'bank_id' => $params['id'],
                    'code'    => $code,
                    'method'  => $method,
                );
            }
            $params["method_codes"] = $methods;
            unset($params['grouped_method_codes']);
        }
        return parent::_prepareParams($params);
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/hd_bccp/bank');
    }
    
}