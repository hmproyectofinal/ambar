<?php $installer = $this;
/* @var $installer Mage_Sales_Model_Mysql4_Setup */
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('winwin_opsintegration_execution_history_info')};
CREATE TABLE {$this->getTable('winwin_opsintegration_execution_history_info')} (
		`id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
                `integration_name`  varchar(255) NOT NULL ,
		`executed_at`  datetime NOT NULL ,
		`processed_file_name`  varchar(255) NOT NULL ,
		`records_processed_correctly`  int(10) NOT NULL ,
		`total_records`  int(10) NOT NULL ,
		`execution_type` varchar(255) NOT NULL ,
		`username`  varchar(255) NULL DEFAULT NULL ,
		`execution_status`  varchar(255) NULL DEFAULT NULL ,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
