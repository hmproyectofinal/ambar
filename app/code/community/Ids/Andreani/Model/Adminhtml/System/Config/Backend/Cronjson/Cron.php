<?php
/**
 * Created by Jhonattan Campo
 * Date: 22/07/16
 * Class Ids_Andreani_Model_Adminhtml_System_Config_Backend_Cronjson_Cron
 * @description Clase que setea el cron en la DB
 */
class Ids_Andreani_Model_Adminhtml_System_Config_Backend_Cronjson_Cron
    extends Mage_Core_Model_Config_Data
{

    const CRON_MODEL_PATH = 'crontab/jobs/andreani/run/model';
    const CRON_STRING_PATH = 'crontab/jobs/andreani/schedule/cron_expr';

    protected function _afterSave()
    {
        $time       = $this->getData('groups/andreaniconfig/fields/cron_schedule/value');
        $frequency  = $this->getData('groups/andreaniconfig/fields/frequency/value');


        $frequencyDaily     = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY;
        $frequencyWeekly    = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
        $frequencyMonthly   = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;

        $cronDayOfWeek = date('N');

        $cronExprArray = array(
            intval($time[1]),                                   //Minutos
            intval($time[0]),                                   //Horas
            ($frequency == $frequencyMonthly) ? '1' : '*',      //Día del mes
            '*',                                                //Mes del año
            ($frequency == $frequencyWeekly) ? '1' : '*',       //Día de la semana
        );
        $cronExprString = join(' ', $cronExprArray);

        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();
            Mage::getModel('core/config_data')
                ->load(self::CRON_MODEL_PATH, 'path')
                ->setValue((string) Mage::getConfig()->getNode(self::CRON_MODEL_PATH))
                ->setPath(self::CRON_MODEL_PATH)
                ->save();
        } catch (Exception $e) {
            throw new Exception(Mage::helper('cron')->__('Hubo un error al generar el cron.'));
        }

    }
}