<?php

class Hd_Bccp_Block_Form_Bcc extends Hd_Bccp_Block_Form
{
    
    protected $_template = 'hd_bccp/form/bcc.phtml';
    
    protected $_jsonControllerName = 'checkout_bcc';
    
    public function getPaymentOptions()
    {
        if(!$this->_paymentOptions) {
            
            $options = $this->_getPaymentOptionsSkeleton();
            
            $options['bcc'] = array(
                'order' => $this->getMethod()->getConfigData('bcc_order'),
            );
            
            // Creditcards
            foreach ($this->getPaymentSource()->getAvailableCreditcards() as $cc) {
            /* @var $cc Hd_Bccp_Model_Creditcard */

                // Creditcard Options
                $ccOptions = array(
                    'label' => $cc->getName(),
                    'value' => $cc->getId(),
                );
                $options['creditcards']['options'][$cc->getId()] = $ccOptions;
                
                // Creditcard Data
                $ccData = $cc->getData();
                unset($ccData['banks']);
                $options['creditcards'][$cc->getId()] = $ccData;
                
                // Workaround Query Error
                if(!$cc->getBanks()) {
                    if(Mage::getIsDeveloperMode()) {
                        Mage::log(__METHOD__ . ' DATA ERROR: Tarjeta Sin Bancos Mapeados');
                        Mage::log($cc->getData());
                    }
                    // Clean Data
                    unset($options['creditcards'][$cc->getId()]);
                    unset($options['creditcards']['options'][$cc->getId()]);
                    // Bye
                    continue;
                }
                
                foreach ($cc->getBanks() as $bank) {
                    
                    // Bank Options
                    $bankOptions = array(
                        'label' => $bank->getName(),
                        'value' => $bank->getId(),
                    );
                    $options['creditcards'][$cc->getId()]['banks']['options'][$bank->getId()] = $bankOptions;
                    
                    if(!isset($options['banks'][$bank->getId()])) {
                        // Bank Data
                        $bankData = $bank->getData();
                        $bankData['creditcards'] = array(
                            'options' => array(),
                        );
                        // Bank Base Data
                        $options['banks'][$bank->getId()] = $bankData;
                        // Bank Options
                        $options['banks']['options'][$bank->getId()] = $bankOptions;
                    }
                    $options['banks'][$bank->getId()]['creditcards']['options'][$cc->getId()] = $ccOptions;
                }
                
            }
            
            $this->_paymentOptions = $options;
            
        }
        
        return $this->_paymentOptions;
            
    }

}
