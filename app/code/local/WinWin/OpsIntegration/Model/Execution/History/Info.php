<?php

class WinWin_OpsIntegration_Model_Execution_History_Info extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix      = 'winwin_opsintegration_execution_history_info';
    protected $_eventObject      = 'execution_history_info';

    const EXECUTION_TYPE_MANUAL = 'manual';
    const EXECUTION_TYPE_AUTOMATIC = 'automatic';

    const EXECUTION_STATUS_SUCCESSFUL = 'successful';
    const EXECUTION_STATUS_ERROR = 'error';

    const EXECUTION_INTEGRATION_PRICES_IMPORT = 'Precios_Import';
    const EXECUTION_INTEGRATION_STOCKS_IMPORT = 'Stocks_Import';
    const EXECUTION_INTEGRATION_STOCKSINC_IMPORT = 'Stocksinc_Import';
    const EXECUTION_INTEGRATION_ORDERS_EXPORT = 'Ordenes_Export';
    const EXECUTION_INTEGRATION_STATES_IMPORT = 'Estados_Import';
    const EXECUTION_INTEGRATION_SHIPMENTS_IMPORT = 'Shipments_Import';

    protected function _construct()
    {
        $this->_init('winwin_opsintegration/execution_history_info');
    }
}