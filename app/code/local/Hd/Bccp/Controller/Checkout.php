<?php
class Hd_Bccp_Controller_Checkout
    extends Hd_Base_Controller_Json
{
    protected function _getTotal()
    {
        return null;
    }
    
    protected function _validateParams()
    {
        if($this->_getParam('method',null) === null) {
            mage::throwException($this->__('Invalid Parameters'));
        }
        if($this->_getParam('cc_id',null) === null) {
            mage::throwException($this->__('Invalid Parameters'));
        }
    }
    
    public function paymentsAction()
    {
        try {
            
            $this->_validateParams();
            
            $method = $this->_getParam('method');
            $bankId = $this->_getParam('bank_id');
            $ccId   = $this->_getParam('cc_id');
            $total  = $this->_getTotal();

            // Load Payments
            $payments = $this->getPaymentSource()
                ->getAvailablePayments(array(
                    'cc_id' => $ccId,
                    'bank_id' => $bankId,
                    'total' => $total,
                ));
            
            // Add Options & Labels
            $payments = $this->getFormHelper()
                ->preparePaymentOptions($payments);
            
            // Response
            $response['status'] = 'ok';
            $response['result']['data'] = $payments;
            
        } catch (Exception $e) {
            $msg = (Mage::getIsDeveloperMode()) ? $e->getMessage() 
                : $this->__('An error occurs getting payments.');
            $this->_errorHandler($msg);
            return;
        }

        $this->_setJsonResponse($response);
        return;
        
    }
    
    /**
     * 
     */
    public function getFormHelper()
    {
        return $this->getPaymentSource()->getFormHelper();                
    }
    
    /**
     * @return Hd_Bccp_Model_Source_Abstract
     */
    public function getPaymentSource()
    {
       $paymentSource = $this->getMethod()->getPaymentSource();
       return $paymentSource;
    }
    
    /**
     * @todo implementar desde helper method
     * 
     * @return Hd_Bccp_Model_Payment_Method_Interface
     */
    public function getMethod()
    {
        // FILTRAR
        $code = $this->_getParam('method');
        $methods = Mage::getSingleton('payment/config')->getActiveMethods();
        if (!$method = @$methods[$code]) {
            Mage::throwException($this->__('Invalid Payment Method'));
        }
        // Interface
        if (!($method instanceof Hd_Bccp_Model_Payment_Method_Interface)) {
            Mage::throwException($this->__('The Payment Method "%s", is not supported'));
        }
        return $method;
    }
    
    protected function _validateAgentsBypass()
    {
        return (Mage::getIsDeveloperMode()) ?: parent::_validateAgentsBypass();
    }
    
}