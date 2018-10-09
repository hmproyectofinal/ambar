<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$conn = $installer->getConnection();

$conn->addColumn($installer->getTable('cms_block'),'fecha_desde', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
    'nullable'  => true,
    'after'     => null,
    'comment'   => 'Fecha Desde'
    ));  

$conn->addColumn($installer->getTable('cms_block'),'fecha_hasta', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
    'nullable'  => true,
    'after'     => null,
    'comment'   => 'Fecha Hasta'
    ));

$installer->endSetup();