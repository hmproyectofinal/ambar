<?php
/**
 * @author 		Vladimir Popov
 * @copyright  	Copyright (c) 2014 Vladimir Popov
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;

$installer->startSetup();

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'print_template_id',
        'TINYINT( 1 ) NOT NULL'
    )
;

$installer->getConnection()
    ->addColumn(
        $this->getTable('webforms'),
        'print_attach_to_email',
        'TINYINT( 1 ) NOT NULL'
    )
;

$installer->endSetup();