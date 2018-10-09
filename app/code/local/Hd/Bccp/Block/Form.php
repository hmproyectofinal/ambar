<?php
class Hd_Bccp_Block_Form extends Mage_Payment_Block_Form
{
    protected $_template;

    protected $_childFormClass;

    protected $_childFormTemplate; 
    
    protected $_jsonControllerName;
    
    protected $_paymentOptions;
    
    protected $_translateKeys = array(
        'Loading Credit Cards',
        'Select Credit Card',
        'Loading Banks',
        'Select Bank',
        'Loading Payment Plans',
        'Loading Payments ...',
        'Select Payment Plan',
    );
    
    protected function _toHtml()
    {
        if($this->canShow()) {
            return parent::_toHtml();
        }
        return '';
    }

    public function canShow()
    {
        $options = $this->getPaymentOptions();
        return count($options['creditcards']);
    }

    public function getChildFormHtml()
    {
        if (!$this->_childFormClass || !$this->_childFormTemplate) {
            return '';
        }
        $block = $this->getLayout()->createBlock(
            $this->_childFormClass
            , $this->getMethodCode() . '-payment-form-child'
            ,  array(
                'template' => $this->_childFormTemplate,
                'method' => $this->getMethod(),
                'method_code' => $this->getMethodCode(),
                'method_name' => $this->getMethodName(),
            )
        );
        return $block->toHtml();
    }
    
    protected function _getPaymentOptionsSkeleton()
    {
        $options = array(
            'code'                  => $this->getMethodCode(),
            'payments_url'          => $this->getPaymentsJsonUrl(),
            'translate'             => array(),
            'creditcards'           => array(),
            'is_backend_checkout'   => $this->_isBackendCheckout(),
        );
        
        if($this->_getProductId()) {
            $options['product_id'] = $this->_getProductId();
        }
        
        // Validate Existing Selection
        if($currentMethod = $this->getCurrentPaymentMethod()) {
            $options['current_method'] = $currentMethod;
        }
        
        // Dinamic Translate build
        foreach ($this->_translateKeys as $key) {
            $options['translate'][$key] = $this->__($key);
        }
        return $options;
    }
    
    public function getPaymentOptions()
    {
        return $this->_getPaymentOptionsSkeleton();
    }

    public function getPaymentOptionsJson()
    {
        return Mage::helper('core')->jsonEncode($this->getPaymentOptions());
    }
    
    public function getCurrentPaymentMethod()
    {
        $quote = $this->getPaymentSource()->getQuote();
        if(!$quote->getPayment()->hasMethodInstance()) {
            return null;
        }
        // Restore Data
        $result = array(
            'method' => $quote->getPayment()->getMethod(),
        );
        foreach($quote->getPayment()->getData() as $key => $value) {
            if(strpos($key, 'hd_bccp_') > -1) {
                $result[$key] = $value;
            }
        }
        
        return $result;
    }
    
    public function getPaymentsJsonUrl()
    {
        $isSecure = Mage::app()->getRequest()->isSecure();
        if($this->_isBackendCheckout()) {
            $url = $this->getUrl("adminhtml/{$this->_jsonControllerName}/payments", array(
                '_secure' => $isSecure,
                '_current'   => true,
            ));
            $url .= '?isAjax=true';
        } else {
            $url = $this->getUrl("hd_bccp/{$this->_jsonControllerName}/payments", array(
                '_secure' => $isSecure
            ));
        }
        return $url;
    }
    
    /**
     * @return Hd_Bccp_Helper_Data
     */
    protected function _paymentHelper()
    {
        return Mage::helper('hd_bccp');
    }
    
    /**
     * @return Hd_Bccp_Model_Source_Bcc
     */
    protected function getPaymentSource()
    {
        return $this->getMethod()->getPaymentSource()
            ->setIsBackendCheckout($this->_isBackendCheckout());
    }
    
    protected function _isBackendCheckout()
    {
        return (Mage::app()->getStore()->getId() == Mage_Core_Model_App::ADMIN_STORE_ID);
    }
    
    protected function _getProductId()
    {
        $product = Mage::registry('current_product');
        if($product) {
            return $product->getId();
        }
    }
    
    /**
     * Set block template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate($this->_template);
    }
    
    /**
     * Override To avoid No Method Error on Payment Preview
     * @return type
     */
    public function getMethodCode()
    {
        if($methodCode = $this->getData('method_code')) {
            // Init Method
            $modelClass = Mage::getStoreConfig("payment/$methodCode/model");
            $this->setData('method', Mage::getModel($modelClass));
            return $methodCode;
        }
        return parent::getMethodCode();
    }
    
}

