<?php

class WinWin_OpsIntegration_Model_Mysql4_Execution_History_Info extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('winwin_opsintegration/execution_history_info', 'id');
    }
}