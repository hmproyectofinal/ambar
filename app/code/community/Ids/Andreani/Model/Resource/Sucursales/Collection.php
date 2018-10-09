<?php
/**
 * Created by Jhonattan Campo.
 * Date: 19/07/16
 * @description collection tabla ids_andreani_json_sucursales
 * Class Ids_Andreani_Model_Resource_Sucursales_Collection
 */
class Ids_Andreani_Model_Resource_Sucursales_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('andreani/sucursales');
    }
}