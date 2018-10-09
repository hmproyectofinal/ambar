<?php
// @var $setup Mage_Eav_Model_Entity_Setup
$setup = $this;

$setup->startSetup();

$setup->addAttribute('customer_address', 'sucursal', array(
        'type'              => 'varchar',
        'input'             => 'text',
        'label'             => 'sucursal',
        'visible'           => true,
        'required'          => true,
        'unique'            => false,
        'sort_order'        => 75, // Positions of the other attributes are listed in
        'position'          => 75, // Mage_Customer_Model_Resource_Setup
        'is_user_defined'   => 1,
        'is_system'         => 0,
        'validate_rules'    => array(
            'max_text_length'   => 255,
        ),
    )
);

// Change the column name into your own attribute name
try{
    $setup->run("
      ALTER TABLE {$this->getTable('sales_flat_quote_address')} ADD COLUMN `sucursal` INT (11) DEFAULT NULL;
      ALTER TABLE {$this->getTable('sales_flat_order_address')} ADD COLUMN `sucursal` INT (11) DEFAULT NULL;
  ");
} catch (Exception $e) {
    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
}

$setup->endSetup();