<?php
$installer = $this;

/** COMENTO LA CREACION DE NUEVOS ESTADOS DE ORDENES**/
/* 

// Required tables
$statusTable = $installer->getTable('sales/order_status');
$statusStateTable = $installer->getTable('sales/order_status_state');
 
// Insert statuses
$installer->getConnection()->insertArray(
    $statusTable,
    array(
        'status',
        'label'
    ),
    array(
  
        array('status' => 'test_todopago_processing', 'label' => 'test_todopago_processing'),
        array('status' => 'test_todopago_complete', 'label' => 'test_todopago_complete'),
        array('status' => 'test_todopago_canceled', 'label' => 'test_todopago_canceled'),
        array('status' => 'test_todopago_offline', 'label' => 'test_todopago_offline'),        
    )
);
 
// Insert states and mapping of statuses to states
$installer->getConnection()->insertArray(
    $statusStateTable,
    array(
        'status',
        'state',
        'is_default'
    ),
    array(
        
        array(
            'status' => 'test_todopago_processing',
            'state' => 'processing',
            'is_default' => 1
        ),
        array(
            'status' => 'test_todopago_complete',
            'state' => 'processing',
            'is_default' => 0
        ),
        array(
            'status' => 'test_todopago_canceled',
            'state' => 'processing',
            'is_default' => 0
        ),
        array(
            'status' => 'test_todopago_canceled',
            'state' => 'new',
            'is_default' => 0
        ),
        array(
            'status' => 'test_todopago_offline',
            'state' => 'processing',
            'is_default' => 0
        )
    )
);

*/
	 