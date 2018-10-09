<?php
$setup = $this;
$setup->startSetup();

$setup->getConnection()
->addColumn($setup->getTable('transactionalemail/carriertemplate'),'store_id', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable'  => false,
    'length'    => 255,
    'after'     => null,
    'comment'   => 'store_id'
    ));   

$setup->getConnection()
->addColumn($setup->getTable('transactionalemail/estadotemplate'),'store_id', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
    'nullable'  => false,
    'length'    => 255,
    'after'     => null,
    'comment'   => 'store_id'
    ));   

$setup->endSetup();