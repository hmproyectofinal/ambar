<?php

class Hd_Bccp_Model_Sales_Order_Creditmemo_Total_Surcharge 
    extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract

{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $address    = $creditmemo->getOrder()->getShippingAddress();
        $amount     = $address->getData('hd_bccp_surcharge');
        $baseAmount = $address->getData('hd_bccp_base_surcharge');
        if ($amount) {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseAmount);
        }
        return $this;
    }
    
}