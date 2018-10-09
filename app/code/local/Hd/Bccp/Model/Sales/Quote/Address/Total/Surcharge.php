<?php

class Hd_Bccp_Model_Sales_Quote_Address_Total_Surcharge 
    extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    protected $_code = 'hd_bccp_surcharge';
    
    /**
     * Determina si calculo y aplico los totales
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @return boolean
     */
    protected function _canCollect(Mage_Sales_Model_Quote_Address $address)
    {
        if (($address->getAddressType() == 'billing')) {
//Mage::log(__METHOD__ . ' SKIP - IS BILLING ADDRESS');
            return false;
        }        
        if (!count($this->_getAddressItems($address))) {
//Mage::log(__METHOD__ . ' SKIP - NO ADDRESS ITEMS');
            return false;
        }        
        if(!$address->getQuote()->getPayment()->hasMethodInstance()) {
//Mage::log(__METHOD__ . ' SKIP - NO PAYMENT INSTANCE');
            return false;
        }
        $payment = $address->getQuote()->getPayment()->getMethodInstance();
        if (!$payment instanceof Hd_Bccp_Model_Payment_Method_Interface) {
//Mage::log(__METHOD__ . ' SKIP - NO HD_BCCP');
            return false;
        }
//Mage::log(__METHOD__ . ' OK');
        return true;
    }

    /**
     * Calcula y Aplica los costos de financiación
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @return \Hd_Bccp_Model_Sales_Quote_Address_Total_Surcharge
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address); 
        
        $this->_resetFields($address);

        if (!$this->_canCollect($address)) {
            // Reset
            return $this;
        }
        
        $quotePayment  = $this->_getQuotePayment($address);
        $paymentSource = $this->_getMethod($address)->getPaymentSource();
         
        //Totals
        $total      = $address->getGrandTotal();
        $baseTotal  = $address->getBaseGrandTotal();
        $surchargeAmount        = $paymentSource->getSurchargeAmount($total, $quotePayment);
        $baseSurchargeAmount    = $paymentSource->getSurchargeAmount($baseTotal, $quotePayment);
        
//Mage::log(__METHOD__ . " TOTAL: {$total}");
//Mage::log(__METHOD__ . " BASE TOTAL: {$baseTotal}");
//Mage::log(__METHOD__ . " SURCHARGE: {$surchargeAmount}");
//Mage::log(__METHOD__ . " BASE SURCHARGE: {$baseSurchargeAmount}");
        
        // Set Surcharge Amount
        $this->_addAmount($surchargeAmount);
        $this->_addBaseAmount($baseSurchargeAmount);
        
        // Set Surcharge Total Amount
        $address->setData('hd_bccp_surcharge', $surchargeAmount);
        $address->setData('hd_bccp_base_surcharge', $baseSurchargeAmount);
        
        // Add to Grand Total
        $address->setData('grand_total', $total + $surchargeAmount);
        $address->setData('base_grand_total', $baseTotal + $baseSurchargeAmount);
        
        return $this;
    }
    
    /**
     * Add surcharge information to address object
     * 
     * @param Mage_Sales_Model_Quote_Address $address 
     * @return Mage_Sales_Model_Quote_Address_Total_Shipping 
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        parent :: fetch($address);
        $amount = $address->getTotalAmount($this->getCode());
        if ($amount != 0) {
            $address->addTotal(array(
                'code'  => $this->getCode(),
                'title' => $this->getLabel(),
                'value' => $amount
            ));
        }
        return $this;
    }
    
    /**
     * Custom Label
     * @return string 
     */
    public function getLabel()
    {
        return Mage :: helper('hd_bccp')->__('Payment Surcharge');
    }
    
    /**
     * Reset Surcharge Data
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @return \Hd_Bccp_Model_Sales_Quote_Address_Total_Surcharge
     */
    protected function _resetFields(Mage_Sales_Model_Quote_Address $address)
    {
        // Reset Surcharge Amount
        $this->_setAmount(0);
        $this->_setBaseAmount(0);
        $address->unsetData('hd_bccp_surcharge');
        $address->unsetData('hd_bccp_base_surcharge');
        return $this;
    }
    
    /**
     * Payment Method Instance
     * @var Hd_Bccp_Model_Payment_Method_Interface | Mage_Payment_Model_Method_Abstract
     */
    protected $_method;
    
    /**
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Hd_Bccp_Model_Payment_Method_Interface | Mage_Payment_Model_Method_Abstract
     */
    protected function _getMethod(Mage_Sales_Model_Quote_Address $address)
    {
        return $this->_getQuotePayment($address)->getMethodInstance();
    }
    
    /**
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Mage_Sales_Model_Quote_Payment
     */
    protected function _getQuotePayment(Mage_Sales_Model_Quote_Address $address)
    {
        return $address->getQuote()->getPayment();
    }


}