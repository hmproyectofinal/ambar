<?php
/**
 * Created by Jhonattan Campo.
 * Date: 19/07/16
 * @description resource tabla ids_andreani_json_sucursales
 * Class Ids_Andreani_Model_Resource_Sucursales
 */
class Ids_Andreani_Model_Resource_Sucursales extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('andreani/sucursales', 'id');
    }
}