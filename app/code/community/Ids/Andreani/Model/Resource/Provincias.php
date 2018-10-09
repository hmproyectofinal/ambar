<?php
/**
 * Created by Jhonattan Campo.
 * Date: 14/07/16
 * @description resource tabla ids_andreani_codpostales_localidades_provincias
 * Class Ids_Andreani_Model_Resource_Provincias
 */
class Ids_Andreani_Model_Resource_Provincias extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('andreani/provincias', 'id');
    }
}