<?php
class Hd_Bccp_Model_Source_Cc extends Hd_Bccp_Model_Source_Abstract
{
    /**
     * Devuelve una coleccion de Tarjetas
     * Solo muestra las tarjetas que:
     * 
     * - Estan asignados al Pais "$countryId" @see Hd_Bccp_Model_Source_Abstract::_getCountryId()
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
            ->addCcAvailabilityFilter($this->getMethodCode(), $countryId, $storeId);
        
//Mage::log(__METHOD__);
//Mage::log($countryId);
//Mage::log($storeId);
//Mage::log($creditcardCollection->getSelectSql(true));
//Mage::log($creditcardCollection->getData());
        
        return $creditcardCollection;
    }
    
     /**
     * 
     * @param int $ccId
     * @param int $bankId
     * @param float $total
     * 
     * @return Hd_Bccp_Model_Resource_Creditcard_Payment_Collection
     */
    public function getAvailablePayments($params)
    {
        $ccId   = $params['cc_id'];
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
            ->addCreditcardFilter($ccId)
            ->addStoreFilter($storeIds)
            ;
        
        $cc = $this->getCreditcard($ccId, $this->getMethodCode(), $countryId);
        
        foreach ($paymentCollection as $payment) {
            $surcharge = $this->_calculateSurcharge($total, $payment->getCoefficient());
            $data = array(
                'id'                    => $payment->getId(),
                'cc_id'                 => $cc->getId(),
                // TOTALS
                'surcharge_amount'      => $surcharge,
                'total_amount'          => $total + $surcharge,
                'payment_amount'        => ($total + $surcharge) / $payment->getPayments(),
                // GATEWAY
                'gateway_cc_code'       => $cc->getGatewayCode(),
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
     * @return float $urcharge
     */
    public function getSurchargeAmount($total, Mage_Sales_Model_Quote_Payment $quotePayment)
    {
        $payment = $this->getPayment($quotePayment->getData('hd_bccp_cc_payment_id'));
        if (!$payment->getId()) {
            Mage::throwException($this->_helper()->__('Invalid Payment Plan.'));
        }
        $surcharge = $this->_calculateSurcharge($total, $payment->getCoefficient());
        return $surcharge;
    }
    
    /**
     * Implementacion
     * 
     * @param Varien_Object $data
     * @param Mage_Sales_Model_Quote_Payment $payment
     * @return Hd_Bccp_Model_Source_Bcc
     */
    public function praparePaymentImportData(Varien_Object $data, Mage_Sales_Model_Quote_Payment $quotePayment)
    {
        $cc      = $this->getCreditcard($data->getCcId());
        $payment = $this->getPayment($data->getCcPaymentId());
        
        // Validación
        if (!$cc->getCreditcardId()) {
            Mage::throwException($this->_helper()->__('Invalid Payment Parameter: Creditcard'));
        }
        if (!$payment->getPaymentId()) {
            Mage::throwException($this->_helper()->__('Invalid Payment Parameter: Payment'));
        }
        
        // Reconstruccion a partir de los datos recibidos
        $preparedData = array(
            'hd_bccp_cc_id'                 => $cc->getId(),
            'hd_bccp_cc_name'               => $cc->getName(),
            'hd_bccp_cc_payment_id'         => $payment->getId(),
            'hd_bccp_payments'              => $payment->getPayments(),
            'hd_bccp_bank_id'               => null,
            'hd_bccp_bank_name'             => null,
            // Gateway Data
            'hd_bccp_gateway_cc_code'       => $cc->getGatewayCode(),
            'hd_bccp_gateway_merchant_code' => $this->getMethod()->getMerchantCode(),
            'hd_bccp_gateway_bank_code'     => null,
            'hd_bccp_gateway_promo_code'    => null,
        );
        
        $quotePayment->addData($preparedData);
        
        return $this;
        
    }
    
}