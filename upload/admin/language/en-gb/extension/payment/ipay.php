<?php
/**
 * English language file for iPay payment module (Admin)
 * 
 * @author flancer.eu
 * @version 1.0.0
 * @license MIT
 */

// Heading
$_['heading_title']         = 'iPay Checkout API';

// Text
$_['text_extension']        = 'Extensions';
$_['text_success']          = 'iPay module settings updated successfully!';
$_['text_edit']             = 'Edit iPay Checkout Module';
$_['text_ipay']             = '<a href="https://www.ipay.ua/" target="_blank"><img src="image/payment/ipay.png" alt="iPay.ua" title="iPay.ua" style="border: 1px solid #EEEEEE; height: 27px;" /></a>';
$_['text_enabled']          = 'Enabled';
$_['text_disabled']         = 'Disabled';
$_['text_yes']              = 'Yes';
$_['text_no']               = 'No';

// Entry
$_['entry_mch_id']          = 'Merchant ID (mch_id)';
$_['entry_mch_key']         = 'Signature Key (sign_key)';
$_['entry_test_mode']       = 'Test Mode';
$_['entry_order_status']    = 'Order Status After Payment';
$_['entry_failed_status']   = 'Order Status On Failure';
$_['entry_geo_zone']        = 'Geo Zone';
$_['entry_status']          = 'Module Status';
$_['entry_sort_order']      = 'Sort Order';
$_['entry_min_total']       = 'Minimum Order Total';

// Help
$_['help_mch_id']           = 'Your unique merchant identifier provided by iPay.ua';
$_['help_mch_key']          = 'Secret signature key provided by iPay.ua (minimum 32 characters)';
$_['help_test_mode']        = 'Enable to use iPay Sandbox for testing. Disable for live transactions.';
$_['help_order_status']     = 'Order status that will be assigned after successful payment';
$_['help_failed_status']    = 'Order status that will be assigned after failed payment';
$_['help_geo_zone']         = 'Select geo zone where this payment method will be available. Leave empty for all zones.';
$_['help_min_total']        = 'Minimum order amount required for this payment method to become active (in default currency)';

// Error
$_['error_permission']      = 'You do not have permission to manage this module!';
$_['error_mch_id']          = 'Merchant ID is required!';
$_['error_mch_key']         = 'Signature Key is required!';
$_['error_mch_id_format']   = 'Merchant ID must be numeric!';
$_['error_mch_key_length']  = 'Signature Key is too short (minimum 32 characters)!';

// Button
$_['button_test_connection'] = 'Test Connection';

// Success
$_['success_connection']    = 'Connection to iPay API successful!';

// Additional text
$_['text_all_zones']        = 'All Geo Zones';
$_['text_api_version']      = 'API Version v1.14';

// URLs
$_['text_callback_url']     = 'Callback URL';
$_['help_callback_url']     = 'Copy this URL to your iPay merchant settings';