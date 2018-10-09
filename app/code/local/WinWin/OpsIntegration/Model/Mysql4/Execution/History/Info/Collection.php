<?php

class WinWin_OpsIntegration_Model_Mysql4_Execution_History_Info_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('winwin_opsintegration/execution_history_info');
    }
}