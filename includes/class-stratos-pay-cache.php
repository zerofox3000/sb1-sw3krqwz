<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Request Caching Handler
 */
class Stratos_Pay_Cache {
    private static $cache_group = 'stratos_pay_requests';
    private static $cache_time = 300; // 5 minutes
    
    /**
     * Initialize cache
     */
    public static function init() {
        if (!wp_using_ext_object_cache()) {
            wp_cache_add_non_persistent_groups(array(self::$cache_group));
        }
    }
    
    /**
     * Get cached response
     */
    public static function get_cached_response($key) {
        return wp_cache_get($key, self::$cache_group);
    }
    
    /**
     * Cache response
     */
    public static function cache_response($key, $data) {
        wp_cache_set($key, $data, self::$cache_group, self::$cache_time);
    }
    
    /**
     * Generate cache key
     */
    public static function generate_key($method, $endpoint, $params = array()) {
        return md5($method . $endpoint . serialize($params));
    }
}