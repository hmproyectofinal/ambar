<?php
class Hd_Nps_Model_Cron
{
    const XML_CONFIG_PATH_STATUS_UPDATE_TIME = 'hd_nps/status_update_cron/time';
    
    const REGISTRY_NS_NPS_CRON = 'hd_nps_is_cron';
    
    protected function _setCronFlag()
    {
        if(!Mage::registry(self::REGISTRY_NS_NPS_CRON)) {
            Mage::register(self::REGISTRY_NS_NPS_CRON, true);
        }
        return $this;
    }
    
    protected function _unsetCronFlag()
    {
        if(Mage::registry(self::REGISTRY_NS_NPS_CRON)) {
            Mage::unregister(self::REGISTRY_NS_NPS_CRON);
        }
        return $this;
    }
    
    public function checkStatusOrdersUpdatePayment()
    {
        
Varien_Profiler::start(__METHOD__);

        $statuses = array(
            Hd_Nps_Model_Psp::ORDER_STATUS_PENDING,
        );
        
        $min    = Mage::app()->getStore()->getConfig(self::XML_CONFIG_PATH_STATUS_UPDATE_TIME);
        $now    = $this->getDateHelper()->getNowUtc();
        $date   = $this->getDateHelper()->formatDatetime($now->subMinute($min));
        
        $collection = $this->_getOrdersResourceCollection()
            ->addFieldToFilter('status',array('in' => $statuses))
            ->addFieldToFilter('updated_at',array('lteq' => $date));
        
        if ($collection->count()) {
            $this->_setCronFlag();
            foreach ($collection as $order) {
                try {
                    
                    $method = $order->getPayment()->getMethodInstance();
                    /* @var $method Hd_Nps_Model_Psp */
                    $method->processCronAction();
                    
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
            $this->_unsetCronFlag();
        }
        
Varien_Profiler::stop(__METHOD__);
        
        return;
    }
    
    
    /**
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _getOrdersResourceCollection()
    {
        return Mage::getResourceModel('sales/order_collection');
    }
    
    /**
     * @return Hd_Base_Helper_Date
     */
    public function getDateHelper()
    {
        return Mage::helper('hd_base/date');
    }
    
}