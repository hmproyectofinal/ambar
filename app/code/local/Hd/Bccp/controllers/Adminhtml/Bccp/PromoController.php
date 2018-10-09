<?php

class Hd_Bccp_Adminhtml_Bccp_PromoController
    extends Hd_Bccp_Controller_Adminhtml_Bccp
{
    
    protected $_gridBlock           = 'hd_bccp/adminhtml_bccp_promo';
    
    protected $_editContentBlock    = 'hd_bccp/adminhtml_bccp_promo_edit';
    
    protected $_editLeftBlock       = 'hd_bccp/adminhtml_bccp_promo_edit_tabs';
    
    protected $_modelClass          = 'hd_bccp/promo';
    
    protected $_entityName          = 'Promotion';
    
    protected $_modelNamespace      = Hd_Bccp_Block_Adminhtml_Bccp_Promo::REGISTRY_MODEL_NAMESPACE;
        
    protected function _initAction()
    {
        // Load Layouts
        parent::_initAction();
        // Subtitle
        $this->_title($this->_helper()->__('Manage Promotions'));
        // Menu
        $this->_setActiveMenu('system/hd_bccp/promo');
        
        return $this;
    }
    
    protected function _backupParams()
    {
        $params = $this->getRequest()->getParams();
        $params = $this->_filterDates($params, array('active_to_date','active_from_date'));
        $params = $this->_filterArray($params, array('active_to_time','active_from_time', 'active_week_days'));
        $this->_getSession()->setData($this->_getModelNamespace(), $params);
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
        // Dates
        $params = $this->_filterDates($params, array('active_to_date','active_from_date'));
        // Arrays(es)
        $params = $this->_filterArray($params, array('active_to_time','active_from_time', 'active_week_days'));
        
        // Prepare Method Codes
        if(isset($params['grouped_method_codes'])) {
            $methods = array();
            foreach ($params['grouped_method_codes'] as $methodCode => $methodData) {
//                if (!empty($methodData['merchant_code']) || !empty($methodData['promo_code'])) {
//                }
                $methods[] = array(
                    'promo_id'      => $params['id'],
                    'merchant_code' => @$methodData['merchant_code'] ?: null,
                    'promo_code'    => @$methodData['promo_code']  ?: null,
                    'method'        => $methodCode,
                );
            }
            if (count($methods)) {
                $params["method_codes"] = $methods;
            }
            unset($params['grouped_method_codes']);
        }
        
        // Clean Unused
        if (@$params['bank_id'] == '') {
            unset($params['bank_id']);
        }
        if (@$params['bank_discount'] == '') {
            unset($params['bank_discount']);
        }
        
        return parent::_prepareParams($params);
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/hd_bccp/promo');
    }
    
}