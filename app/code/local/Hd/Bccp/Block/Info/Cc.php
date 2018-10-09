<?php

class Hd_Bccp_Block_Info_Cc extends Mage_Payment_Block_Info
{

    protected $_customTemplate = 'hd_bccp/info/cc.phtml';

    /**
     * Set block template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate($this->_customTemplate);
    }

    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $info = $this->getInfo();
        $transport = parent::_prepareSpecificInformation(new Varien_Object());
        $transport->addData(array(
            Mage::helper('hd_bccp')->__('Credit Card') => $info->getData('hd_bccp_cc_name'),
            Mage::helper('hd_bccp')->__('Payments') => $info->getData('hd_bccp_payments'),
        ));
        return $transport;
    }

}
