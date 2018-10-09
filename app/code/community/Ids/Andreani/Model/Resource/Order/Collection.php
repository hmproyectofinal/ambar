<?php
class Ids_Andreani_Model_Resource_Order_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
 {
     public function _construct()
     {
         parent::_construct();
         $this->_init('andreani/order');
     }
}