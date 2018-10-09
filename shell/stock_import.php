<?php

require_once 'brandlive_abstract.php';

class Integration_Stock_Import extends Brandlive_Shell_Abstract
{
	public $files_to_process;
    public function run(){
 		Mage::helper('winwin_opsintegration/data')->setUserIsCron(true);
		Mage::getModel('winwin_opsintegration/Stockimporterp')->getCsvStockFileToMagento($this->files_to_process);
    }
   
}

$shell = new Integration_Stock_Import();
$shell->files_to_process = $shell->getArg('f');
$shell->run();
