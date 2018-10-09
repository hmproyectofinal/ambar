<?php
class Ids_Andreani_Model_Resource_Order extends Mage_Core_Model_Mysql4_Abstract
{
     public function _construct()
     {
         $this->_init('andreani/order', 'id');
     }
}