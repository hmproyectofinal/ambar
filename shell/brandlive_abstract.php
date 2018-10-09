<?php
require_once 'abstract.php';

abstract class Brandlive_Shell_Abstract extends Mage_Shell_Abstract {
     public function __construct(){
        if ($this->_includeMage) {
            require_once $this->_getRootPath() . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';
            Mage::app($this->_appCode, $this->_appType);
        }    
        $this->_parseArgs();
        $this->_construct();
        $this->_validate();
        $this->_showHelp();
    }
}