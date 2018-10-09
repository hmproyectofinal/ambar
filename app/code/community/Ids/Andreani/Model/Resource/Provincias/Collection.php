<?php
/**
 * Created by Jhonattan Campo.
 * Date: 14/07/16
 * @description collection tabla ids_andreani_codpostales_localidades_provincias
 * Class Ids_Andreani_Model_Resource_Provincias_Collection
 */
class Ids_Andreani_Model_Resource_Provincias_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('andreani/provincias');
    }
}