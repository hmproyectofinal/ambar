<?php
$setup = $this;

$setup->startSetup();
$setup->run("
    CREATE TABLE IF NOT EXISTS `{$setup->getTable('ecloud_estado_template')}` (
        `id` int(11) NOT NULL AUTO_INCREMENT UNIQUE PRIMARY KEY,
        `estado_code` varchar(100) NOT NULL,
        `email_template` varchar(100) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$setup->endSetup();