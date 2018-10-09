<?php

/* Unfortunate fix for Magento 1.4.1.1 way of loading the Adminhtml controllers. */

require_once 'app' . DS . 'code' . DS . 'local' . DS . 'WinWin' . DS . 'OpsIntegration' . DS . 'controllers' . DS . 'Adminhtml' . DS . 'Winwin' . DS . 'Integration' . DS . 'Execution' . DS . 'HistoryController.php';

class WinWin_OpsIntegration_WinWin_Integration_Execution_HistoryController extends WinWin_OpsIntegration_Adminhtml_WinWin_Integration_Execution_HistoryController
{

    /* DO NOT ADD ANY METHODS HERE! USE THE PARENT CLASS! */

//    public function indexAction()
//    {
//        echo 'You should not see me :)';
//        exit;
//    }
}
