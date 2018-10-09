<?php
class Hd_Bccp_Controller_Product
    extends Hd_Bccp_Controller_Checkout
{
    protected function _validateParams()
    {
        $total = $this->_getParam('total');
        $productId = $this->_getParam('product_id');
        if(is_null($productId) && is_null($total)) {
            mage::throwException($this->__('Invalid Parameters'));
        }
        return parent::_validateParams();
    }
    
    protected function _getTotal()
    {
        if($total = $this->_getParam('total',null)) {
            return (float)$total;
        }
        
        if($productId = $this->_getParam('product_id',null)) {
            $product = Mage::getModel('catalog/product')->load($productId);
            return (!is_null($product)) ? $product->getPrice() : null;
        }
        
        
        
    }
    
}