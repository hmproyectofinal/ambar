<?php
class Hd_Bccp_Block_Adminhtml_Sales_Order_Creditmemo_Totals
    extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_Totals
{
    protected function _initTotals()
    {
        parent::_initTotals();
        
        $address    = $this->getSource()->getShippingAddress();
        $amount     = $address->getData('hd_bccp_surcharge');
        $baseAmount = $address->getData('hd_bccp_base_surcharge');
        if ($amount != 0) {
            $this->addTotal(new Varien_Object(
                array(
                    'code'       => 'hd_bccp_surcharge',
                    'value'      => $amount,
                    'base_value' => $baseAmount,
                    'label'      => $this->helper('hd_bccp')->__('Payment Surcharge'),
                ), 
                array('shipping'))
            );
        }
        return $this;
    }
}
