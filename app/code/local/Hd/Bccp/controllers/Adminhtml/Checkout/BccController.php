<?php
class Hd_Bccp_Adminhtml_Checkout_BccController
    extends Hd_Bccp_Controller_Adminhtml_Checkout
{
    protected function _validateParams()
    {
        parent::_validateParams();
        if($this->_getParam('bank_id',null) === null) {
            mage::throwException($this->__('Invalid Parameters'));
        }
    }
    
}