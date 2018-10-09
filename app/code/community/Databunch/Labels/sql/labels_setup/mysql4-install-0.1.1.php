<?php

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'featured_product', array(
    'group'             => 'General',
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Featured product',
    'input'             => 'boolean',
    'class'             => '',
    'source'            => '',
    'is_global'         => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => true,
    'default'           => '0',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'unique'            => false,
    'apply_to'          => 'simple,configurable,virtual,bundle,downloadable',
    'is_configurable'   => false,
));

$installer->updateAttribute('catalog_product', 'featured_product', 'used_in_product_listing', '1');
$installer->addAttributeToGroup('catalog_product', 'Default', 'Default', 'related_brands');

$installer->endSetup();