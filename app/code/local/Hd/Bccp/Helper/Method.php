<?php

class Hd_Bccp_Helper_Method extends Hd_Bccp_Helper_Data
{
    const XML_CONFIG_PATH_ALLOWED_METHODS = 'hd_bccp/methods';
    
    /**
     * @todo Revisar Implementacion Detalladamente:
     * - Se utiliza desde el controller que devuelve los planes
     * - Se utiliza en el Observer para determinar si el metodo es de Lc Payment
     * 
     * @param string $methodCode
     */
    public function getMethod($code = null)
    {
        $code = $code ?: Mage::app()->getRequest()->getParam('method');
        if ($methods = $this->_getAllowedMethods()) {
            // Validate Active Methods
            if (!$method = @$methods[$code]) {
                return null;
//                Mage::throwException($this->__('Invalid Payment Method'));
            }
            // Validate Method Interface
            if (!($method instanceof Hd_Bccp_Model_Payment_Method_Interface)) {
                return null;
//                Mage::throwException($this->__('The Payment Method "%s", is not supported'));
            }
            return $method;
        }
        return null;
    }
    
    /**
     * @return boolean
     */
    protected function _getAllowedMethods()
    {
        $methods = Mage::getSingleton('payment/config')->getActiveMethods();
//        $methods = Mage::getStoreConfig(self::XML_CONFIG_PATH_ALLOWED_METHODS);
        if (!$methods) {
            return null;
        }
        return $methods;
    }  
    
}
