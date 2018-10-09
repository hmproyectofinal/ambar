<?php
/**
 * Mensaje de Cornejo, por lo que se va a sacar la obligatoriedad al campo a nivel base de datos, pero se mantendra en
 * los formularios el rquiered del campo para validacion JS
 *
 * "Con respecto al DNI, el Tag es obligatorio, pero el campo puede estar vacío. Por lo tanto podes hacer
 * la llamada con el campo vacio.
 * Saludos."
 */
$setup = $this;
$setup->startSetup();

$setup->run("
    UPDATE eav_attribute
    SET is_required = 0
    WHERE attribute_code = 'dni'
");

$setup->endSetup();

