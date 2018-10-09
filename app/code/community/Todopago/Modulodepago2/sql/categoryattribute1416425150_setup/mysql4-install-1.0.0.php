<?php
$installer = $this;
$installer->startSetup();


$installer->addAttribute("catalog_product", "todopagofechaevento",  array(
    "type"     => "datetime",
    "backend"  => "eav/entity_attribute_backend_datetime",
    "frontend" => "",
    "label"    => "Fecha del Evento",
    "input"    => "date",
    "class"    => "",
    "source"   => "",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
    "visible_on_front"  => false,
    "unique"     => false,
    "note"       => "",
    'group' => 'Prevención de fraude'

	));

$installer->addAttribute("catalog_product", "todopagocodigo",  array(
    "type"     => "int",
    "backend"  => "",
    "frontend" => "",
    "label"    => "Código del Producto",
    "input"    => "select",
    "class"    => "",
    "source"   => "modulodepago2/eav_entity_attribute_source_categoryoptions14164251503",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
	
    "visible_on_front"  => false,
    "unique"     => false,
    "note"       => "",
    'group' => 'Prevención de fraude'

	));

$installer->addAttribute("catalog_product", "todopagoenvio",  array(
    "type"     => "int",
    "backend"  => "",
    "frontend" => "",
    "label"    => "Tipo de Envío",
    "input"    => "select",
    "class"    => "",
    "source"   => "modulodepago2/eav_entity_attribute_source_categoryoptions14164251504",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
	
    "visible_on_front"  => false,
    "unique"     => false,
    "note"       => "",
    'group' => 'Prevención de fraude'

	));

$installer->addAttribute("catalog_product", "todopagoservicio",  array(
    "type"     => "int",
    "backend"  => "",
    "frontend" => "",
    "label"    => "Tipo de servicio",
    "input"    => "select",
    "class"    => "",
    "source"   => "modulodepago2/eav_entity_attribute_source_categoryoptions14164251505",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
	
    "visible_on_front"  => false,
    "unique"     => false,
    "note"       => "",
    'group' => 'Prevención de fraude'

	));

$installer->addAttribute("catalog_product", "todopagodelivery",  array(
    "type"     => "int",
    "backend"  => "",
    "frontend" => "",
    "label"    => "Tipo de Delivery",
    "input"    => "select",
    "class"    => "",
    "source"   => "modulodepago2/eav_entity_attribute_source_categoryoptions14164251506",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
	
    "visible_on_front"  => false,
    "unique"     => false,
    "note"       => "",
    'group' => 'Prevención de fraude'

	));
$installer->endSetup();
	 