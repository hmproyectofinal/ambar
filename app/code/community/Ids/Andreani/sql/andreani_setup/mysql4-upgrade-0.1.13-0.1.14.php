<?php
/**
 *Created Jhonattan Campo
 * <jcampo@ids.net.ar>
 * Date: 04/08/16
 */

$setup = $this;
$setup->startSetup();

$setup->run("ALTER TABLE sales_flat_shipment
                    ADD COLUMN andreani_datos_guia mediumblob");

$setup->endSetup();

