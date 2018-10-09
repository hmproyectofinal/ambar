<?php
class Hd_Bccp_Helper_Form extends Hd_Bccp_Helper_Data
{
    
    public function preparePaymentOptions($payments)
    {
        $result = array();
        foreach ($payments as $payment) {
            
            $label = $this->__(
                ($payment->getPayments() > 1) ? '%s payments of %s. ' : '%s payment of %s. '
                , $payment->getPayments()
                , $this->formatPrice($payment->getPaymentAmount())
            );
            
            // Surcharge Detail
            if ($payment->getSurchargeAmount() != 0) {
                $label .= $this->__(
                    '(Surcharge %s)'
                    , $this->formatPrice($payment->getSurchargeAmount())
                );
            }
            
            // Payment Data
            // Usamos como Identificador la key "payments" ya que es numerico
            // mientras en en payment_id puede venir un String en el caso de Promo
            $result[$payment->getPayments()] = $payment->getData();
            // Option
            $result['options'][$payment->getPayments()] = array(
                'value' => $payment->getPayments(),
                'label' => $label,
            );
        }
        return $result;
    }
    
    public function formatPrice($price)
    {
        $app = Mage::app();
        $code = $app->getStore()->getCurrentCurrencyCode();
        return str_replace(' ', '' ,$app->getLocale()->currency($code)->toCurrency($price));
    }
    
}

