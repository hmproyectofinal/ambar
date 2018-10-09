<?php

class Hd_Bccp_Model_Resource_Creditcard_Payment 
    extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init("hd_bccp/creditcard_payment", "payment_id");
    }
    
}
