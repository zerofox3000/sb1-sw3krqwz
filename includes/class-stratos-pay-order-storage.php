<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * High-Performance Order Storage Handler
 */
class Stratos_Pay_Order_Storage {
    private static $cache_group = 'stratos_pay_orders';
    private static $cache_time = 3600; // 1 hour
    
    /**
     * Initialize order storage
     */
    public static function init() {
        // Add custom table for high-performance order storage
        self::create_tables();
        
        // Hook into WooCommerce order creation
        add_action('woocommerce_new_order', array(__CLASS__, 'store_order'), 10, 1);
        add_action('woocommerce_update_order', array(__CLASS__, 'update_stored_order'), 10, 1);
        
        // Initialize object cache if available
        if (!wp_using_ext_object_cache()) {
            wp_cache_add_non_persistent_groups(array(self::$cache_group));
        }
    }
    
    /**
     * Create custom tables for order storage
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}stratos_pay_order_meta (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            meta_key varchar(255) NOT NULL,
            meta_value longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY meta_key (meta_key),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Store order data in high-performance storage
     */
    public static function store_order($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;
        
        $order_data = array(
            'id' => $order_id,
            'total' => $order->get_total(),
            'currency' => $order->get_currency(),
            'status' => $order->get_status(),
            'payment_method' => $order->get_payment_method(),
            'customer_id' => $order->get_customer_id(),
            'billing_email' => $order->get_billing_email(),
            'created_at' => $order->get_date_created()->getTimestamp(),
        );
        
        // Store in object cache
        wp_cache_set($order_id, $order_data, self::$cache_group, self::$cache_time);
        
        // Store in custom table
        self::store_order_meta($order_id, $order_data);
    }
    
    /**
     * Store order metadata in custom table
     */
    private static function store_order_meta($order_id, $data) {
        global $wpdb;
        
        foreach ($data as $key => $value) {
            $wpdb->insert(
                $wpdb->prefix . 'stratos_pay_order_meta',
                array(
                    'order_id' => $order_id,
                    'meta_key' => $key,
                    'meta_value' => is_array($value) ? serialize($value) : $value
                ),
                array('%d', '%s', '%s')
            );
        }
    }
    
    /**
     * Get order data from high-performance storage
     */
    public static function get_order($order_id) {
        // Try object cache first
        $order_data = wp_cache_get($order_id, self::$cache_group);
        
        if (false === $order_data) {
            // Fetch from custom table
            $order_data = self::get_order_from_storage($order_id);
            
            if ($order_data) {
                // Store in cache for future requests
                wp_cache_set($order_id, $order_data, self::$cache_group, self::$cache_time);
            }
        }
        
        return $order_data;
    }
    
    /**
     * Get order data from custom table
     */
    private static function get_order_from_storage($order_id) {
        global $wpdb;
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_key, meta_value FROM {$wpdb->prefix}stratos_pay_order_meta WHERE order_id = %d",
                $order_id
            ),
            ARRAY_A
        );
        
        if (!$results) return false;
        
        $order_data = array();
        foreach ($results as $row) {
            $value = $row['meta_value'];
            if (@unserialize($value) !== false) {
                $value = unserialize($value);
            }
            $order_data[$row['meta_key']] = $value;
        }
        
        return $order_data;
    }
    
    /**
     * Update stored order data
     */
    public static function update_stored_order($order_id) {
        // Clear cache
        wp_cache_delete($order_id, self::$cache_group);
        
        // Re-store order data
        self::store_order($order_id);
    }
}