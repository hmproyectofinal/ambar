<?php
class Hd_Nps_Controller_Front extends Mage_Core_Controller_Front_Action
{
    /**
     * @var Mage_Sales_Model_Order 
     */
    protected $_order;
    
    /**
     * @var Hd_Nps_Model_Psp 
     */
    protected $_method;
    
    protected function _isValidCheckout()
    {
        $session = $this->_getCheckoutSession();
        // Nps Quote Id
        if(!$session->getNpsQuoteId()) {
            return false;
        }
        return true;
    }
    
    public function evaluateAction()
    {
        // Valid Checkout
        if(!$this->_isValidCheckout()) {
            $this->_forward('failure');
            return;
        }
        
        try {
            
            // Parametros del Request
//            $transactionId = $this->getRequest()->getParam('psp_TransactionId', null);
//            if (!$transactionId) {
//                $transactionId = $this->_getMethod()->getOrderPspTransactionId();
//            }
            
            $result = $this->_getMethod()
                ->processEvaluateAction();
            
            if (!$result) {
                $this->_forward('failure');
                return;
            }
            
            // Todo OK
            $this->_clearCheckoutData()
                ->_redirect('checkout/onepage/success');
            
        } catch (Exception $e) {
            $this->_errorHandler($e);
        }
    }
    
    public function cancelAction()
    {
        if(!$this->_isValidCheckout()) {
            $this->_forward('failure');
            return;
        }
        
        $message = $this->__('Payment transaction canceled by customer.');
        $this->_getMethod()->processCancelAction($message);
        
        $this->_forward('failure');
        return;
    }
    
    public function failureAction()
    {
        // Redirect
        $this->_clearCheckoutData()
            ->_redirect('checkout/onepage/failure');
        
        return;
    }
    
    protected function _clearCheckoutData()
    {
        if($this->_isValidCheckout()) {
            
            // Unset Quote
            $this->_getCheckoutSession()
                ->getQuote()->setIsActive(false)->save();

            // Unset Quote Id
            $this->_getCheckoutSession()
                ->unsQuoteId()
                ->unsNpsQuoteId();
            
        }
        
        
        return $this;
    }
    
    /**
     * Handle Exceptions trhown during processing
     * 
     * @param Exception $e
     * @return type
     */
    protected function _errorHandler(Exception $e)
    {
        $message = (Mage::getIsDeveloperMode()) ? $e->getMessage()
            : $this->__('An error occurs during payment transaction.');
        
        // Save Error
        Mage::logException($e);
        
        $this->_forward('failure');
        return;
    }
    
    /**
     * @return Hd_Nps_Model_Psp
     */
    protected function _getMethod()
    {
        if (!$this->_method) {
            
            if(!$payment = $this->_getOrder()->getPayment()){
                throw new Exception($this->__('No Payment Received.'));
            }
                
            if(!$method = $payment->getMethodInstance()){
                throw new Exception($this->__('No Payment Method Received.'));
            }
            
            if (!$method instanceof Hd_Nps_Model_Psp) {
                throw new Exception($this->__('Invalid Payment Method Type.'));
            }
            
            $this->_method = $method;
            
        }
        return $this->_method;
    }
    
    /**
     * @return  Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        if (!$this->_order) {
            
            // if(Mage::getIsDeveloperMode()) {
            //     Mage::log(__METHOD__);
            //     Mage::log("Action nps/{$this->getRequest()->getControllerName()}/{$this->getRequest()->getActionName()}");
            //     Mage::log("NPS QUOTE ID: {$this->_getCheckoutSession()->getNpsQuoteId()}");
            //     Mage::log("QUOTE ID: {$this->_getCheckoutSession()->getQuoteId()}");
            // }
            
            $orderId = $this->_getCheckoutSession()
                ->getData('last_real_order_id');
        
            if (!$orderId) {
                throw new Exception($this->__('No Order Received.'));
            }
            
            // Order
            $this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        }
        return $this->_order;
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }
    
}

