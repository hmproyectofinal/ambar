<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */

/* @var $this Mage_Core_Model_Resource_Setup */
$this->startSetup();

$this->run("
CREATE TABLE `{$this->getTable('amsorting/revenue')}` (
  `id` int(10) unsigned NOT NULL COMMENT 'Id',
  `store_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store Id',
  `revenue` decimal(9,4) NOT NULL COMMENT 'Revenue',
  KEY `IDX_AM_SORTING_REVENUE_REVENUE` (`id`,`store_id`)
) ENGINE=MyISAM COMMENT='Index Table revenue';
");

$this->endSetup();