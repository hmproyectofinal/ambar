<?php

class Ecloud_TransactionalEmail_Model_Carriertemplate extends Mage_Core_Model_Abstract
{
    protected function _construct(){
       $this->_init("transactionalemail/carriertemplate");
    }

    public function loadByCarrier($carrier, $store_id){
        $collection = $this->getCollection()
                ->addFieldToFilter('carrier_code', $carrier)
                ->addFieldToFilter('store_id', $store_id);
        return $collection->getFirstItem();
    }
}