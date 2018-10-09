<?php

$installer = $this;

$installer->startSetup();

$installer->run(" 
ALTER TABLE {$this->getTable('storepickup_store')}
 ADD COLUMN `pickup_enabled` smallint(11) not null default 1;
");

$installer->endSetup(); 
