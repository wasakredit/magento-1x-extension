<?php

$installer = $this;

// Required tables
$statusTable = $installer->getTable('sales/order_status');
$statusStateTable = $installer->getTable('sales/order_status_state');

// Insert statuses
$installer->getConnection()->insertArray(
$statusTable,
array('status', 'label'),
  array(
    array(
      'status' => 'pending_wasa_checkout', 
      'label' => 'Pending Wasa Checkout'
    )
  )
);

// Statuses -> states mappings 
$installer->getConnection()->insertArray(
  $statusStateTable,
  array('status', 'state', 'is_default'),
  array(
    array(
      'status' => 'pending_wasa_checkout', 
      'state' => 'pending_wasa_checkout', 
      'is_default' => 1
    )
  )
);