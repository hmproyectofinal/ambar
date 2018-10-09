<?php
class Hd_Nps_PayController extends Hd_Nps_Controller_Front
{
    protected function _getAllowedHttpReferrers()
    {
        $allowedUrls = array();
        
        // Nps Urls
        $urls = array_values(Mage::getStoreConfig('hd_nps/web_service/urls'));
        foreach($urls as $url) {
            $pUrl = parse_url($url);
            $allowedUrls[] = "{$pUrl['scheme']}://{$pUrl['host']}";
        }
        
        // local Only from nps/pay
        $allowedUrls[] = trim(Mage::getUrl('nps/pay', array('_secure' => $this->getRequest()->isSecure())), '/');
                
        return $allowedUrls;
    }
    
    protected function _isAllowedReferrer($referrer)
    {
        foreach ($this->_getAllowedHttpReferrers() as $allowed) {
            if(strpos($referrer,$allowed) === 0) {
                return true;
            }
        }
        return true;
    }
    
    protected function _isIframeChild()
    {
        // Valid Referrer
        if(!$referrer = $this->getRequest()->getServer('HTTP_REFERER')) {
            return false;
        }
        // Valid Url
        if(!$this->_isAllowedReferrer($referrer)) {
            return false;
        }
        return true;
    }
    
    protected function _initIframeChild()
    {
        // Validate from Iframe
        if(!$this->_isIframeChild()) {
            $this->_forward('failure');
            return;
        }
        
        // Load XML
        $this->loadLayout()
            ->renderLayout();
    }

    public function indexAction()
    {
        if(!$this->_isValidCheckout()) {
            $this->_forward('failure');
            return;
        }
        $this->loadLayout()->renderLayout();
    }
    
    public function formAction()
    {
        try {
            
            if(!$this->_isValidCheckout() || !$this->_isIframeChild()) {
                throw new Exception($this->__('Invalid Checkout.'));
            }
            
            $this->loadLayout()->getLayout()->getBlock('nps.redirect.form')
                ->setMethod($this->_getMethod());
            $this->renderLayout();
            
        } catch (Exception $e) {
            // Error Handler
            $this->_forward('error');
        }
        return;
    }
    
    public function returnAction()
    {
        $this->_initIframeChild();
    }
    
    public function declineAction()
    {
        $this->_initIframeChild();
    }
    
    public function errorAction()
    {
        $this->_initIframeChild();
    }
    

}
