<?php

class Ecloud_TransactionalEmail_Model_Estadotemplate extends Mage_Core_Model_Abstract
{
    protected function _construct(){
       $this->_init("transactionalemail/estadotemplate");
    }

    public function loadByEstado($estado, $store_id){
        $collection = $this->getCollection()
                ->addFieldToFilter('estado_code', $estado)
                ->addFieldToFilter('store_id', $store_id);
        return $collection->getFirstItem();
    }
}