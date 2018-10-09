<?php

class Hd_Nps_Model_System_Config_Source_Order_Status 
    extends Mage_Adminhtml_Model_System_Config_Source_Order_Status
{

    protected $_stateStatuses = array(
        Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
    );

}
