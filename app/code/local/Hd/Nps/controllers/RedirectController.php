<?php
class Hd_Nps_RedirectController extends Hd_Nps_Controller_Front
{
    
    public function indexAction()
    {
        $this->_forward('form');
    }
    
    public function formAction()
    {
        try {
            
            if(!$this->_isValidCheckout()) {
                throw new Exception($this->__('Invalid Checkout.'));
            }
            
            $this->loadLayout()->getLayout()->getBlock('nps.redirect.form')
                ->setMethod($this->_getMethod());
            
            $this->renderLayout();
            
        } catch (Exception $e) {
            // Error Handler
            $this->_errorHandler($e);
        }
        return;
    }
            
    public function returnAction()
    {
        $this->_forward('evaluate');
        return;
    }
    
    public function declineAction()
    {
        $this->_forward('cancel');
        return;
    }
    

    
}
