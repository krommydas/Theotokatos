<?php
// Heading
$_['heading_title']      = 'Attika Bank E-Payment';

// Text
$_['text_payment']       = 'Payment';
$_['text_success']       = 'Success: You have modified attika bank e-payment details!';

// Entry
$_['entry_total']        = 'Total:<br /><span class="help">The checkout total the order must reach before this payment method becomes active. Leave empty for not set</span>';
$_['entry_geo_zone']     = 'Geo Zone:';
$_['entry_secret']       = 'Secret:';
$_['entry_store_id']     = 'Store ID: ';
$_['entry_status']       = 'Status:';
$_['entry_sort_order']   = 'Sort Order:<br /><span class="help"> The order appearance of the payment. Leave empty for not set</span>';

// Order Statuses
$_['order_status_created']   = 'Created Order Status:<br /><span class="help">The order status after confirming payment method</span>';
$_['order_status_pending']   = 'Pending Order Status:<br /><span class="help">The order status when awaiting payment</span>';
$_['order_status_succeeded'] = 'Succeded Order Status: ';
$_['order_status_failed']    = 'Failed Order Status: ';

// Error
$_['error_permission']   = 'Warning: You do not have permission to modify payment attika bank e-payment!';
$_['error_hung_orders']   = 'Error: Hung orders detected. Uninstall rolled back! Please insert settings again';
$_['error_curreny']      = 'Your shop must have one of the following currencies installed and enabled:<br>"EUR", "USD"';
$_['error_required_total']      = 'The required checkout total must be a positive number';
$_['error_sort_order']      = 'The sort order must be an integer greater from 0';
$_['error_status']      = 'Please select one from the available status';
$_['error_order_status']      = 'Please select one from the available order status';
$_['error_order_status_duplicate']      = 'You can not have duplicate order status';
$_['error_store_id']      = 'You must specify a store id';
$_['error_secret']      = 'You must specify a store secret';
$_['error_geo_zone']      = 'Please select one from the available geo zones';
$_['error_malicious_data']      = 'You provided unnecessary data';
?>