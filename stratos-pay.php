<?php
/**
 * Plugin Name: Stratos Pay
 * Plugin URI: https://stratospay.com/
 * Description: Accept payments and manage transactions with Stratos Pay merchant services.
 * Version: 1.0.8
 * Author: Stratos Pay
 * Author URI: https://stratospay.com/
 * Text Domain: stratos-pay
 * Domain Path: /languages
 * WC requires at least: 5.0
 * WC tested up to: 8.2
 * Requires PHP: 7.2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('STRATOS_PAY_VERSION', '1.0.8');
define('STRATOS_PAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('STRATOS_PAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('STRATOS_PAY_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once STRATOS_PAY_PLUGIN_DIR . 'includes/class-stratos-pay.php';
require_once STRATOS_PAY_PLUGIN_DIR . 'includes/class-stratos-pay-transactions.php';

// Initialize the plugin
function stratos_pay_init() {
    // Check WooCommerce dependency
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'stratos_pay_woocommerce_notice');
        return;
    }
    
    // Initialize plugin
    $stratos_pay = new Stratos_Pay();
    $stratos_pay->init();
    
    // Initialize WooCommerce integration
    if (class_exists('WC_Payment_Gateway')) {
        require_once STRATOS_PAY_PLUGIN_DIR . 'includes/class-stratos-pay-wc-gateway.php';
        add_filter('woocommerce_payment_gateways', 'stratos_pay_add_gateway');
    }

    // Declare HPOS compatibility
    add_action('before_woocommerce_init', function() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
        }
    });
}
add_action('plugins_loaded', 'stratos_pay_init');

// WooCommerce dependency notice
function stratos_pay_woocommerce_notice() {
    ?>
    <div class="notice notice-error">
        <p><?php _e('Stratos Pay requires WooCommerce to be installed and active.', 'stratos-pay'); ?></p>
    </div>
    <?php
}

// Add the gateway to WooCommerce
function stratos_pay_add_gateway($gateways) {
    $gateways[] = 'WC_Stratos_Pay_Gateway';
    return $gateways;
}

// Register activation hook
register_activation_hook(__FILE__, 'stratos_pay_activate');
function stratos_pay_activate() {
    // Check WooCommerce dependency
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(__('Stratos Pay requires WooCommerce to be installed and active.', 'stratos-pay'));
    }
    
    // Trigger welcome page redirect on activation
    set_transient('stratos_pay_activation_redirect', true, 30);
    
    // Initialize default settings
    $default_settings = array(
        'test_public_key' => '',
        'test_secret_key' => '',
        'public_key' => '',
        'secret_key' => '',
        'webhook_secret' => '',
        'environment' => 'sandbox'
    );
    
    update_option('stratos_pay_settings', $default_settings);
    
    // Create transactions table
    Stratos_Pay_Transactions::create_table();
}

// Register deactivation hook
register_deactivation_hook(__FILE__, 'stratos_pay_deactivate');
function stratos_pay_deactivate() {
    // Clear any scheduled hooks or temporary data
    delete_transient('stratos_pay_activation_redirect');
}

// Add settings link on plugin page
add_filter('plugin_action_links_' . STRATOS_PAY_PLUGIN_BASENAME, 'stratos_pay_settings_link');
function stratos_pay_settings_link($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=stratos_pay') . '">' . __('Settings', 'stratos-pay') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}