<?php

class Hd_Bccp_Model_Observer extends Varien_Object
{
    
    protected $_preparedDataFlag = 'is_payment_data_prepared';
    
    /**************************************************************************/
    /***************************************************** Listeners Methods **/
    /**************************************************************************/
    
//    public function adminhtmlSalesOrderCreateProcessData(Varien_Event_Observer $observer)
//    {
//        $orderModel = $observer->getEvent()->getData('order_create_model');
//        $postData   = $observer->getEvent()->getData('request');
//        
//        // Validate Payment
//        if(!@$paymentData = $postData['payment']) {
//            return;
//        }
//        // Validate Payment Data
//        if(@$paymentData['cc_id'] == '' || @$paymentData['payments'] == '') {
//            return;
//        }
//        
//        $payment = $orderModel->getQuote()->getPayment();
//        $data = new Varien_Object($paymentData);
//        
//        // Prepare Data & Save Quote
//        $this->_importPaymentData($data, $payment, true);
//        
//        return;
//        
//    }
//    
//    public function adminhtmlSalesQuoteCollectTotalsBefore(Varien_Event_Observer $observer)
//    {
//        // Save Quote just before collect totals
//        if($this->_preparedDataFlag) {
//            $observer->getEvent()->getQuote()->save();
//        }
//    }
    
    public function quotePaymentImportDataBefore(Varien_Event_Observer $observer)
    {
        $data    = $observer->getEvent()->getInput();
        $payment = $observer->getEvent()->getPayment();
        
        $this->_importPaymentData($data, $payment);
    }
    
    /**************************************************************************/
    /****************************************************** Internal Methods **/
    /**************************************************************************/
    
    protected function _importPaymentData(Varien_Object $data,Mage_Sales_Model_Quote_Payment $payment, $saveQuote = false)
    {
        // Validate Method
        if(!$method = Mage::helper('hd_bccp/method')->getMethod($data->getMethod())) {
            return;
        }

        // Prepare Data
        $method->getPaymentSource()
            ->praparePaymentImportData($data, $payment);
        
        // Set as prepared for use later
        $this->_preparedDataFlag = true;
        
        // Save Quote to force callect totals
        if($saveQuote) {
            $payment->getQuote()->save();
        }
        
        return;
    }
    
    public function addRuleConditions(Varien_Event_Observer $observer)
    {
        $additional = $observer->getAdditional();
        $conditions = (array)$additional->getConditions();
        // Creditcard
        $conditions = array_merge_recursive($conditions, array(
            array('label'=> Mage::helper('hd_bccp')->__('Payment Options - Creditcard'), 'value' => 'hd_bccp/rule_condition_creditcard'),
        ));
        // Bank
        $conditions = array_merge_recursive($conditions, array(
            array('label'=> Mage::helper('hd_bccp')->__('Payment Options - Bank'), 'value' => 'hd_bccp/rule_condition_bank'),
        ));
        $additional->setConditions($conditions);
        $observer->setAdditional($additional);
        return $observer;
    }
    
}

