<?php
/* @var $installer Hd_Bccp_Model_Resource_Setup */

$installer = $this;
$installer->_initSetup();

$salesInstaller = $this->getSalesSetupModel();

$attributesData = array(
    array(
        'entities' => array(
            'quote_payment',
            'order_payment',
        ),
        'attributes' => array(
            // Bccp Attributes
            "hd_bccp_cc_id"                 => array("type"=>"int", "grid"=>"false"),
            "hd_bccp_cc_name"               => array("type"=>"varchar", "grid"=>"true"),
            "hd_bccp_cc_payment_id"         => array("type"=>"int", "grid"=>"false"),
            "hd_bccp_bank_id"               => array("type"=>"int", "grid"=>"false"),
            "hd_bccp_bank_name"             => array("type"=>"varchar", "grid"=>"true"),
            "hd_bccp_payments"              => array("type"=>"int", "grid"=>"true"),
            // Bccp Gateway Attributes
            "hd_bccp_gateway_cc_code"       => array("type"=>"varchar", "grid"=>"false"),
            "hd_bccp_gateway_bank_code"     => array("type"=>"varchar", "grid"=>"false"),
            "hd_bccp_gateway_merchant_code" => array("type"=>"varchar", "grid"=>"false"),
            "hd_bccp_gateway_promo_code"    => array("type"=>"varchar", "grid"=>"false"),
        ),
    ),
    array(
        'entities' => array(
            'quote',
            'order',
            'quote_address',
            'order_address',
        ),
        'attributes' => array(
            "hd_bccp_surcharge"             => array("type"=>"decimal", "grid"=>"false"),
            "hd_bccp_base_surcharge"        => array("type"=>"decimal", "grid"=>"false"),
        ),
    ),
);

foreach($attributesData as $group) {
    foreach($group['entities'] as $entity) {
        foreach($group['attributes'] as $code => $data) {
            $salesInstaller->addAttribute($entity, $code, $data);
        }
    }
}

$installer->_endSetup();
