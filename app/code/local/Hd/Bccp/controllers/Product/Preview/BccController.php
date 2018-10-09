<?php
class Hd_Bccp_Product_Preview_BccController
    extends Hd_Bccp_Controller_Product
{
    protected function _validateParams()
    {
        if($this->_getParam('bank_id',null) === null) {
            mage::throwException($this->__('Invalid Parameters'));
        }
        return parent::_validateParams();
    }
    
}