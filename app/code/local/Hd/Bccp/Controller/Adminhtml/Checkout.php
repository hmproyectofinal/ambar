<?php
class Hd_Bccp_Controller_Adminhtml_Checkout
    extends Hd_Base_Controller_Adminhtml_Json
{
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
            
            $bankId = $this->_getParam('bank_id');
            $ccId   = $this->_getParam('cc_id');
            
            // Load Payments
            $paymentsCollection = $this->getPaymentSource()
                ->getAvailablePayments(array(
                    'cc_id' => $ccId,
                    'bank_id' => $bankId,
                ));
            
            // Add Options & Labels
            $payments = $this->getFormHelper()
                ->preparePaymentOptions($paymentsCollection);
            
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
     * @return \Hd_Bccp_Model_Source_Abstract
     */
    public function getPaymentSource()
    {
        if($method = $this->getMethodHelper()->getMethod()) {
            $paymentSource = $method->getPaymentSource()
                ->setIsBackendCheckout(true);
            return $paymentSource;
        }
        return null;
    }
    
    /**
     * @return Hd_Bccp_Helper_Form
     */
    public function getFormHelper()
    {
        return $this->getPaymentSource()->getFormHelper();                
    }
    
    /**
     * @return Hd_Bccp_Helper_Method
     */
    public function getMethodHelper()
    {
        return Mage::helper('hd_bccp/method');
    }
    
    
}