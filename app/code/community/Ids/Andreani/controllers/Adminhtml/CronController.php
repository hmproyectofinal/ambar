<?php
/**
 * Created by Jhonattan Campo
 * Date: 19/07/16
 * Class Ids_Andreani_Adminhtml_CronController
 * @description Controlador que ejecuta el cron para generar el json de sucursales
 * Class Ids_Andreani_Adminhtml_CronController
 */
class Ids_Andreani_Adminhtml_CronController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Cron Json Sucursales'))->_title($this->__('Cron Json Sucursales'));
        $this->cronAction();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function cronAction()
    {
        try
        {
            Mage::getModel('andreani/cron')->run();
            Mage::getSingleton('adminhtml/session')->addSuccess('Json generado correctamente.');

        }
        catch (Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError('Error Generando Json de Sucursales.');
        }
       
    }
}