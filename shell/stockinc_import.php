<?php

require_once 'brandlive_abstract.php';

class Integration_StockInc_Import extends Brandlive_Shell_Abstract
{
    public function run(){
 		Mage::helper('winwin_opsintegration/data')->setUserIsCron(true);
		Mage::getModel('winwin_opsintegration/Stockimportincerp')->getCsvStockFileToMagento();
    }
   
}

$shell = new Integration_StockInc_Import();
$shell->run();

