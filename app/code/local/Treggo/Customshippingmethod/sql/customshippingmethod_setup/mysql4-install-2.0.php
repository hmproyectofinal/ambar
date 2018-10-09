<?php

$installer = $this;
$installer->startSetup();

$installer->addAttribute("order", "treggo_order_status", array("type"=>"varchar", "grid" => false));
$installer->addAttribute("quote", "treggo_order_status", array("type"=>"varchar", "grid" => false));
$installer->endSetup();