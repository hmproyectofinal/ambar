<?php

class WinWin_OpsIntegration_Model_System_Config_Source_Order_Processingstatuses
{
    public function toOptionArray()
    {                       
        return Mage::getResourceModel('sales/order_status_collection')
                    ->addStateFilter(Mage_Sales_Model_Order::STATE_PROCESSING)                    
                    ->toOptionArray();        
    }
}