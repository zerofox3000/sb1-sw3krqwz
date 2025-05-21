<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stratos Pay Admin Class
 */
class Stratos_Pay_Admin {
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize admin hooks
    }
    
    /**
     * Initialize admin hooks
     */
    public function init() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Add admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Add activation redirect
        add_action('admin_init', array($this, 'activation_redirect'));
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
        
        // Add settings validation
        add_filter('pre_update_option_stratos_pay_settings', array($this, 'validate_settings'), 10, 2);
    }
    
    /**
     * Add admin menu items
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Stratos Pay', 'stratos-pay'),
            __('Stratos Pay', 'stratos-pay'),
            'manage_options',
            'stratos-pay-welcome',
            array($this, 'welcome_page'),
            'dashicons-money-alt',
            30
        );
        
        // Welcome/Dashboard submenu
        add_submenu_page(
            'stratos-pay-welcome',
            __('Dashboard', 'stratos-pay'),
            __('Dashboard', 'stratos-pay'),
            'manage_options',
            'stratos-pay-welcome',
            array($this, 'welcome_page')
        );
        
        // Transactions submenu
        add_submenu_page(
            'stratos-pay-welcome',
            __('Transactions', 'stratos-pay'),
            __('Transactions', 'stratos-pay'),
            'manage_options',
            'stratos-pay-transactions',
            array($this, 'transactions_page')
        );
        
        // Disputes submenu
        add_submenu_page(
            'stratos-pay-welcome',
            __('Disputes', 'stratos-pay'),
            __('Disputes', 'stratos-pay'),
            'manage_options',
            'stratos-pay-disputes',
            array($this, 'disputes_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'stratos-pay-welcome',
            __('Settings', 'stratos-pay'),
            __('Settings', 'stratos-pay'),
            'manage_options',
            'stratos-pay-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'stratos-pay') === false) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'stratos-pay-admin',
            STRATOS_PAY_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            STRATOS_PAY_VERSION
        );
        
        // JS
        wp_enqueue_script(
            'stratos-pay-admin',
            STRATOS_PAY_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            STRATOS_PAY_VERSION,
            true
        );
        
        // Add localized script data
        wp_localize_script(
            'stratos-pay-admin',
            'stratosPay',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('stratos_pay_nonce')
            )
        );
    }
    
    /**
     * Activation redirect to welcome page
     */
    public function activation_redirect() {
        // Check if we should redirect
        if (get_transient('stratos_pay_activation_redirect')) {
            delete_transient('stratos_pay_activation_redirect');
            
            // Only redirect if we're not already doing an action
            if (!isset($_GET['activate-multi']) && !wp_doing_ajax()) {
                wp_safe_redirect(admin_url('admin.php?page=stratos-pay-welcome'));
                exit;
            }
        }
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting(
            'stratos_pay_settings',
            'stratos_pay_settings',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_settings')
            )
        );
    }
    
    /**
     * Sanitize settings before save
     */
    public function sanitize_settings($settings) {
        if (!is_array($settings)) {
            return array();
        }
        
        $sanitized = array();
        
        // Sanitize each setting
        $sanitized['environment'] = isset($settings['environment']) ? sanitize_text_field($settings['environment']) : 'sandbox';
        $sanitized['test_public_key'] = isset($settings['test_public_key']) ? sanitize_text_field($settings['test_public_key']) : '';
        $sanitized['test_secret_key'] = isset($settings['test_secret_key']) ? sanitize_text_field($settings['test_secret_key']) : '';
        $sanitized['public_key'] = isset($settings['public_key']) ? sanitize_text_field($settings['public_key']) : '';
        $sanitized['secret_key'] = isset($settings['secret_key']) ? sanitize_text_field($settings['secret_key']) : '';
        $sanitized['webhook_secret'] = isset($settings['webhook_secret']) ? sanitize_text_field($settings['webhook_secret']) : '';
        
        return $sanitized;
    }
    
    /**
     * Validate settings before save
     */
    public function validate_settings($new_value, $old_value) {
        $environment = isset($new_value['environment']) ? $new_value['environment'] : 'sandbox';
        
        // Validate based on environment
        if ($environment === 'sandbox') {
            if (empty($new_value['test_public_key']) || empty($new_value['test_secret_key'])) {
                add_settings_error(
                    'stratos_pay_settings',
                    'missing_test_keys',
                    __('Test mode API keys are required.', 'stratos-pay'),
                    'error'
                );
                return $old_value;
            }
        } else {
            if (empty($new_value['public_key']) || empty($new_value['secret_key'])) {
                add_settings_error(
                    'stratos_pay_settings',
                    'missing_live_keys',
                    __('Live mode API keys are required.', 'stratos-pay'),
                    'error'
                );
                return $old_value;
            }
        }
        
        // Validate webhook secret
        if (empty($new_value['webhook_secret'])) {
            add_settings_error(
                'stratos_pay_settings',
                'missing_webhook_secret',
                __('Webhook secret is required.', 'stratos-pay'),
                'error'
            );
            return $old_value;
        }
        
        return $new_value;
    }
    
    /**
     * Welcome/Dashboard page
     */
    public function welcome_page() {
        require_once STRATOS_PAY_PLUGIN_DIR . 'admin/pages/welcome.php';
    }
    
    /**
     * Transactions page
     */
    public function transactions_page() {
        require_once STRATOS_PAY_PLUGIN_DIR . 'admin/pages/transactions.php';
    }
    
    /**
     * Disputes page
     */
    public function disputes_page() {
        require_once STRATOS_PAY_PLUGIN_DIR . 'admin/pages/disputes.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        require_once STRATOS_PAY_PLUGIN_DIR . 'admin/pages/settings.php';
    }
}