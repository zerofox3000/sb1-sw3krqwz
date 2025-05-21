<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Stratos Pay Plugin Class
 */
class Stratos_Pay {
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize plugin
    }
    
    /**
     * Initialize plugin components
     */
    public function init() {
        // Load text domain for internationalization
        add_action('init', array($this, 'load_textdomain'));
        
        // Check if we need to load admin functionality
        if (is_admin()) {
            $this->init_admin();
        }
    }
    
    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('stratos-pay', false, dirname(STRATOS_PAY_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * Initialize admin functionality
     */
    public function init_admin() {
        require_once STRATOS_PAY_PLUGIN_DIR . 'admin/class-stratos-pay-admin.php';
        $admin = new Stratos_Pay_Admin();
        $admin->init();
    }
}