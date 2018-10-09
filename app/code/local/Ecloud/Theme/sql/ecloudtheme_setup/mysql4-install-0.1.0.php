<?php 
$installer = $this;
$setup = Mage::getResourceModel('catalog/eav_mysql4_setup','core_setup');
$installer->startSetup();
$setup->removeAttribute(Mage_Catalog_Model_Category::ENTITY, 'category_related');
$setup->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'category_related', array(
    'group'         => 'General Information',
    'input'         => 'text',
    'type'          => 'text',
    'label'         => 'Related Category ID',
    'backend'       => '',
    'visible'       => true,
    'required'      => false,
    'visible_on_front' => true,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

$setup->endSetup();
?>