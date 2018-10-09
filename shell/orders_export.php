<?php

require_once 'brandlive_abstract.php';

class Integration_Orders_Export extends Brandlive_Shell_Abstract
{
    public function run(){
 		Mage::helper('winwin_opsintegration/data')->setUserIsCron(true);
		Mage::getModel('winwin_opsintegration/Orderexporterp')->getCsvFileToErp();
    }
   
}

$shell = new Integration_Orders_Export();
$shell->run();

