<?php

//class Hd_Bccp_Model_Source_Bcc extends Hd_Bccp_Model_Source_Abstract
class Hd_Bccp_Model_Source_Bcc extends Hd_Bccp_Model_Source_Cc
{
    
    /**
     * Devuelve una coleccion de Tarjetas con sus bancos asociados
     * Solo muestra las tarjetas y bancos que:
     * 
     * - Estan asignados al Pais "$countryId" @see Hd_Bccp_Model_Source_Abstract::_getCountryId()
     * - Estan asignados al Store "$storeId" @see Hd_Bccp_Model_Source_Abstract::_getStoreId()
     * - Tienen los Codigos del Gateway Mapeados
     * - Tienen al menos una cuota configurada
     * 
     * @return Hd_Bccp_Model_Resource_Creditcard_Collection
     */
    public function getAvailableCreditcards()
    {
        $countryId = ($this->_helper()->isCountrySupportEnable())
            ? $this->_getCountryId()
            : null;
        
        $storeId = ($this->_helper()->isStoreSupportEnable())
            ? $this->_getStoreId()
            : null;
        
        $creditcardCollection = $this->_getCreditcardCollection()
            ->addBccAvailabilityFilter($this->getMethodCode(), $countryId, $storeId)
            ->addBanksToResult();
        
        return $creditcardCollection;
    }
    
    /**
     * @param array $params
     * 
     * @return Hd_Bccp_Model_Resource_Creditcard_Payment_Collection
     */
    public function getAvailablePayments($params)
    {
        $ccId   = $params['cc_id'];
        $bankId = $params['bank_id'];
        $total  = (isset($params['total'])) ? $params['total'] : $this->getQuoteTotal();
        
        $countryId = ($this->_helper()->isCountrySupportEnable())
            ? $this->_getCountryId()
            : null;
        
        $storeIds = ($this->_helper()->isStoreSupportEnable())
            ? $this->_getStoreId()
            : null;
            
        $paymentCollection = $this->_getPaymentCollection();
        $paymentCollection
            ->setMethodCode($this->getMethodCode())
            ->addCountryFilter($countryId)
            ->addCreditcardBankFilter($ccId, $bankId)
            ->addStoreFilter($storeIds)
            ;
        
        $cc   = $this->getCreditcard($ccId, $this->getMethodCode(), $countryId);
        $bank = $this->getBank($bankId, $this->getMethodCode(), $countryId);
        
        foreach ($paymentCollection as $payment) {
            $surcharge = $this->_calculateSurcharge($total, $payment->getCoefficient());
            $data = array(
                'id'                    => $payment->getId(),
                'cc_id'                 => $cc->getId(),
                'bank_id'               => $bank->getId(),
                // TOTALS
                'surcharge_amount'      => $surcharge,
                'total_amount'          => $total + $surcharge,
                'payment_amount'        => ($total + $surcharge) / $payment->getPayments(),
                // GATEWAY
                'gateway_cc_code'       => $cc->getGatewayCode(),
                'gateway_bank_code'     => $bank->getGatewayCode(),
                'gateway_merchant_code' => ($payment->getMerchantCode()) ? $payment->getMerchantCode() : $this->getMethod()->getMerchantCode(),
            );
            if ($payment->getPromoData()) {
                $data['gateway_promo_code'] =  $payment->getPromoCode();
            }
            $payment->addData($data);
        }
        return $paymentCollection;
    }
    
    /**
     * Returns the surcharge amount for given total
     * 
     * @param float $total
     * @param Mage_Sales_Model_Quote_Payment $payment
     * @return float
     */
//    public function getSurchargeAmount($total, Mage_Sales_Model_Quote_Payment $quotePayment)
//    {
//        $payment = $this->getPayment($quotePayment->getData('hd_bccp_cc_payment_id'));
//        if (!$payment->getId()) {
//            Mage::throwException($this->_helper()->__('Invalid Payment Plan.'));
//        }
//        $surcharge = $this->_calculateSurcharge($total, $payment->getCoefficient());
//        return $surcharge;
//    }
    
    /**
     * Prepare Data Before Import to Payment
     * 
     * @param Varien_Object $data
     * @param Mage_Sales_Model_Quote_Payment $payment
     * @return Hd_Bccp_Model_Source_Bcc
     */
    public function praparePaymentImportData(Varien_Object $data, Mage_Sales_Model_Quote_Payment $quotePayment)
    {
        $cc      = $this->getCreditcard($data->getCcId());
        $bank    = $this->getBank($data->getBankId());
        $payment = $this->getPayment($data->getCcPaymentId());
        
        // Validación
        if (!$cc->getCreditcardId()) {
            Mage::throwException($this->_helper()->__('Invalid Payment Parameter: Creditcard'));
        }
        if (!$payment->getPaymentId()) {
            Mage::throwException($this->_helper()->__('Invalid Payment Parameter: Payment'));
        }
        if (!$bank->getBankId()) {
            Mage::throwException($this->_helper()->__('Invalid Payment Parameter: Bank'));
        }
        
        // Reconstruccion a partir de los datos recibidos
        $preparedData = array(
            'hd_bccp_cc_id'                 => $cc->getId(),
            'hd_bccp_cc_name'               => $cc->getName(),
            'hd_bccp_cc_payment_id'         => $payment->getId(),
            'hd_bccp_payments'              => $payment->getPayments(),
            'hd_bccp_gateway_cc_code'       => $cc->getGatewayCode(),
            // Differs From CC
            'hd_bccp_bank_id'               => $bank->getId(),
            'hd_bccp_bank_name'             => $bank->getName(),
            'hd_bccp_gateway_bank_code'     => $bank->getGatewayCode(),
            'hd_bccp_gateway_promo_code'    => $payment->getPromoCode(),
            'hd_bccp_gateway_merchant_code' => ($payment->getMerchantCode()) ? $payment->getMerchantCode() : $this->getMethod()->getMerchantCode(),
        );
        
        $quotePayment->addData($preparedData);
        
        return $this;
        
    }
    

}

