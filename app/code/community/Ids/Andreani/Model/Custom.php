<?php
/**
 * Created by Jhonattan Campo
 * Date: 07/07/16
 */

/**
 * Class Ids_Andreani_Model_Custom
 * @description clase que extiende del backend file, y contiene métodos
 * que permiten la carga específica de imágenes para el logo de la empresa.
 */
class Ids_Andreani_Model_Custom extends Mage_Adminhtml_Model_System_Config_Backend_File
{

    public function save()
    {
        $file = $this->getValue();
        if(isset($file['value']))
        {
            unlink(Mage::getBaseDir()."/media/uploads/".$file['value']);
        }

        return parent::save();
    }

    protected function _getAllowedExtensions()
    {
        return array('png','jpeg','jpg','gif');
    }

}
