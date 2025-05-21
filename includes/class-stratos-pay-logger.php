<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Logging and Monitoring
 */
class Stratos_Pay_Logger {
    private static $log_directory;
    
    /**
     * Initialize logger
     */
    public static function init() {
        self::$log_directory = WP_CONTENT_DIR . '/stratos-pay-logs';
        
        if (!file_exists(self::$log_directory)) {
            wp_mkdir_p(self::$log_directory);
        }
    }
    
    /**
     * Log message
     */
    public static function log($level, $message, $context = array()) {
        if (!self::$log_directory) {
            self::init();
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = sprintf(
            "[%s] %s: %s %s\n",
            $timestamp,
            strtoupper($level),
            $message,
            !empty($context) ? json_encode($context) : ''
        );
        
        $filename = self::$log_directory . '/' . date('Y-m-d') . '.log';
        error_log($log_entry, 3, $filename);
        
        // Send critical errors to Sentry
        if ($level === 'critical') {
            self::notify_sentry($message, $context);
        }
    }
    
    /**
     * Send error to Sentry
     */
    private static function notify_sentry($message, $context) {
        $settings = get_option('stratos_pay_settings');
        if (empty($settings['sentry_dsn'])) {
            return;
        }
        
        wp_remote_post($settings['sentry_dsn'], array(
            'body' => json_encode(array(
                'message' => $message,
                'extra' => $context,
                'level' => 'error'
            )),
            'headers' => array('Content-Type' => 'application/json')
        ));
    }
}