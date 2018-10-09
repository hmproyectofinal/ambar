<?php

class Hd_Bccp_Block_Form_Cc extends Hd_Bccp_Block_Form
{
    
    protected $_template = 'hd_bccp/form/cc.phtml';
    
    protected $_jsonControllerName = 'checkout_cc';
    
    public function getPaymentOptions()
    {
        if(!$this->_paymentOptions) {
            
            $options     = $this->_getPaymentOptionsSkeleton();
            $creditcards = $this->getPaymentSource()->getAvailableCreditcards();

            // Creditcards
            foreach ($creditcards as $cc) {
                // Creditcard Options
                $options['creditcards']['options'][$cc->getId()] = array(
                    'label' => $cc->getName(),
                    'value' => $cc->getId(),
                );
                // Creditcard Data
                $ccData = $cc->getData();
                $options['creditcards'][$cc->getId()] = $ccData;
            }

            // DEBUG
    //        Mage::log(__METHOD__);
    //        Mage::log($options);

            $this->_paymentOptions = $options;
            
        }
        return $this->_paymentOptions;
        
    }
    
}
