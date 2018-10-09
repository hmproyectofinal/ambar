<?php

require_once 'brandlive_abstract.php';

class Integration_Shipments_Import extends Brandlive_Shell_Abstract
{
    public function run(){
 		Mage::helper('winwin_opsintegration/data')->setUserIsCron(true);
		Mage::getModel('winwin_opsintegration/Shipmentimport')->getCsvShipmentFileToMagento();
    }
   
}

$shell = new Integration_Shipments_Import();
$shell->run();
