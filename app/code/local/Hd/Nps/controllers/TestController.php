<?php
class Hd_Nps_TestController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        if (!Mage::getIsDeveloperMode()) {
            die('Access Denied');
        }
        return parent::preDispatch();
    }
    
    public function removeMockAction()
    {
        $setup = new Hd_Nps_Model_Resource_Setup('hd_nps_setup');
        $setup->removeMockData();
        
        die(__METHOD__);
    }
    
    public function createMockAction()
    {
        $setup = new Hd_Nps_Model_Resource_Setup('hd_nps_setup');
        $setup->createMockData();
        
        die(__METHOD__);
    }
    
    public function fraudAction()
    {
        $mobileDetect = new Mobile_Detect();
        
        $orderId = $this->getRequest()->getParam('order_id');
        
        if (!$orderId) {
            die('Declarame un order_id, capo...');
        }
        
        $order = Mage::getModel('sales/order')->load($orderId);
        /* @var $order Mage_Sales_Model_Order */
        
        
        $method = $order->getPayment()->getMethodInstance();
        /* @var $method Hd_Nps_Model_Psp */
        
        $fraud = $method->getFraudProcessor();
        Zend_Debug::dump('FRAUD DATA');
        
        Zend_Debug::dump($fraud->getFraudPspData());
        
        Zend_Debug::dump('ORDER:');
        $od = $order->getData();
        ksort($od);
        Zend_Debug::dump($od);
        
        Zend_Debug::dump('SHIPPING:');
        $sad = $order->getShippingAddress()->getData();
        ksort($sad);
        Zend_Debug::dump($sad);
        
        Zend_Debug::dump('BILLING:');
        $bad = $order->getShippingAddress()->getData();
        ksort($bad);
        Zend_Debug::dump($bad);
        
        if(!$order->getCustomerIsGuest()) {
            Zend_Debug::dump('CUSTOMER:');
            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
            $cd = $customer->getData();
            ksort($cd);
            Zend_Debug::dump($cd);
        }
        
        $scsf = Mage::getModel('hd_nps/system_config_source_fraud');
        /* @var $scsf Hd_Nps_Model_System_Config_Source_Fraud */
//        Zend_Debug::dump($scsf->customerOptions());
//        Zend_Debug::dump($scsf->customerAddressOptions());
        
        die(__METHOD__);        
    }
    
    public function configBuilder($array)
    {
        $data = "\n";
        foreach ($array as $k => $v) {
            $data .= htmlentities("<{$k}>A</{$k}>\n");
        }
        return $data;
    }

    /**
     * @return Hd_Nps_Model_Soap_Client
     */
    protected function _getSoapCLient()
    {
        return Mage::getSingleton('hd_nps/soap_client');
    }

    
    /**
     * @return Hd_Base_Helper_Date
     */
    protected function _dHelper()
    {
        return Mage::helper('hd_base/date');
    }
    
}
