<?php

class Hd_Bccp_Model_Sales_Order_Invoice_Total_Surcharge 
    extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $address    = $invoice->getOrder()->getShippingAddress();
        $amount     = $address->getData('hd_bccp_surcharge');
        $baseAmount = $address->getData('hd_bccp_base_surcharge');
        if ($amount) {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $amount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseAmount);
        }
        return $this;
    }
    
}