<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rate Limiter for API Requests
 */
class Stratos_Pay_Rate_Limiter {
    private static $cache_key_prefix = 'stratos_rate_limit_';
    private static $window_seconds = 3600; // 1 hour
    private static $max_requests = 1000;
    
    /**
     * Check if request is within rate limits
     */
    public static function check_rate_limit($identifier) {
        $cache_key = self::$cache_key_prefix . $identifier;
        $current_time = time();
        $window_start = $current_time - self::$window_seconds;
        
        // Get current request count
        $requests = get_transient($cache_key);
        if (false === $requests) {
            $requests = array();
        }
        
        // Remove old requests
        $requests = array_filter($requests, function($timestamp) use ($window_start) {
            return $timestamp >= $window_start;
        });
        
        // Check limit
        if (count($requests) >= self::$max_requests) {
            return false;
        }
        
        // Add current request
        $requests[] = $current_time;
        set_transient($cache_key, $requests, self::$window_seconds);
        
        return true;
    }
}