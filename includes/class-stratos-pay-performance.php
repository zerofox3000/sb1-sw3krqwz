<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Performance Optimizations for Stratos Pay
 */
class Stratos_Pay_Performance {
    /**
     * Initialize performance optimizations
     */
    public static function init() {
        // Initialize order storage
        Stratos_Pay_Order_Storage::init();
        
        // Add performance hooks
        add_action('init', array(__CLASS__, 'init_performance_features'));
        add_filter('woocommerce_payment_complete_order_status', array(__CLASS__, 'optimize_order_status_change'), 10, 3);
    }
    
    /**
     * Initialize performance features
     */
    public static function init_performance_features() {
        // Enable late order processing
        add_filter('woocommerce_defer_transactional_emails', '__return_true');
        
        // Optimize database queries
        add_filter('woocommerce_order_query_args', array(__CLASS__, 'optimize_order_queries'));
    }
    
    /**
     * Optimize order status changes
     */
    public static function optimize_order_status_change($status, $order_id, $order) {
        // Store status change in high-performance storage
        Stratos_Pay_Order_Storage::update_stored_order($order_id);
        
        return $status;
    }
    
    /**
     * Optimize order queries
     */
    public static function optimize_order_queries($query_args) {
        // Add query optimization for high-volume stores
        $query_args['no_found_rows'] = true;
        $query_args['update_post_meta_cache'] = false;
        $query_args['update_post_term_cache'] = false;
        
        return $query_args;
    }
}