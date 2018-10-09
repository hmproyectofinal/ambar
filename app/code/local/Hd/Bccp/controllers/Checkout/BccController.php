<?php
class Hd_Bccp_Checkout_BccController
    extends Hd_Bccp_Controller_Checkout
{
    protected function _validateParams()
    {
        parent::_validateParams();
        if($this->_getParam('bank_id',null) === null) {
            mage::throwException($this->__('Invalid Parameters'));
        }
    }
    
}