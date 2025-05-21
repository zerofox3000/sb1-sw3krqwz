<?php
// If uninstall is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('stratos_pay_settings');

// Delete any transients
delete_transient('stratos_pay_activation_redirect');

// Note: In a real-world scenario, you might want to consider adding an option
// to keep or delete transaction data when uninstalling