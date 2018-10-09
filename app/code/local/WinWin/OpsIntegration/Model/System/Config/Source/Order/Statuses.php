<?php


class WinWin_OpsIntegration_Model_System_Config_Source_Order_Statuses
{
    public function toOptionArray()
    {
                
        $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
        
        foreach($statuses as $key => $value) {
            $optionArray[] = array('value'=> $key, 'label'=> $value);
        }
//        $optionArray[] = array('value'=> 'processed', 'label'=> 'Processed');
        /**
         * 
         * http://stackoverflow.com/questions/2442230/php-getting-unique-values-of-a-multidimensional-array
         * @var unknown_type
         */
        $unique = array_map('unserialize', array_unique(array_map('serialize', $optionArray)));
        return $optionArray;
    }
}
