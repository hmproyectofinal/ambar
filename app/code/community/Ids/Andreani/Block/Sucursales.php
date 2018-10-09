<?php
/**
 * Created by Jhonattan Campo
 * Date: 19/07/16
 * Class Ids_Andreani_Block_Sucursales
 * @description Bloque de sucursales para ser insertado en los lugares
 * necesarios.
 */
class Ids_Andreani_Block_Sucursales extends Mage_Core_Block_Template
{

    /**
     * @description obtiene las surcursales y las devuelve al bloque
     * @return mixed
     */
    public function getSucursalesData()
    {
        return json_decode(Mage::helper('andreani')->getJsonSucursales());
    }

    /**
     * @description obtiene las variables de sesión de Andreani
     * @return mixed
     */
    public function getSucursalAndreaniSession()
    {
        return Mage::getSingleton('core/session')->getAndreaniSucursal();
    }
    
    
}


