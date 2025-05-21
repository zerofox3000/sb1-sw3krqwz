<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stratos Pay Helper Class
 */
class Stratos_Pay_Helper {
    /**
     * Flag a transaction for investigation
     * 
     * @param string $transaction_id The transaction ID to flag
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public static function flag_transaction($transaction_id) {
        $settings = get_option('stratos_pay_settings');
        $api_key = $settings['environment'] === 'production' ? $settings['secret_key'] : $settings['test_secret_key'];
        
        $response = wp_remote_post(self::get_api_url() . '/transactions/' . $transaction_id . '/flag', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            )
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_Error(
                'api_error',
                isset($body['message']) ? $body['message'] : __('Failed to flag transaction', 'stratos-pay')
            );
        }
        
        // Update local transaction status
        Stratos_Pay_Transactions::flag_transaction($transaction_id);
        
        return true;
    }
    
    /**
     * Validate API key format
     * 
     * @param string $api_key The API key to validate
     * @return bool True if valid, false otherwise
     */
    public static function validate_api_key($api_key) {
        return !empty($api_key) && strlen($api_key) >= 20;
    }
    
    /**
     * Check if API keys are configured
     * 
     * @return bool True if configured, false otherwise
     */
    public static function is_api_configured() {
        $settings = get_option('stratos_pay_settings', array());
        $environment = isset($settings['environment']) ? $settings['environment'] : 'sandbox';
        
        if ($environment === 'sandbox') {
            return !empty($settings['test_public_key']) && !empty($settings['test_secret_key']);
        } else {
            return !empty($settings['public_key']) && !empty($settings['secret_key']);
        }
    }
    
    /**
     * Get API base URL based on environment
     * 
     * @return string The API base URL
     */
    public static function get_api_url() {
        $settings = get_option('stratos_pay_settings', array(
            'environment' => 'sandbox'
        ));
        
        return $settings['environment'] === 'production' 
            ? 'https://api.stratospay.com/v1'
            : 'https://sandbox-api.stratospay.com/v1';
    }
    
    /**
     * Format currency amount
     * 
     * @param float $amount The amount to format
     * @param string $currency The currency code
     * @return string Formatted amount
     */
    public static function format_currency($amount, $currency = 'USD') {
        if ($currency === 'USD') {
            return '$' . number_format($amount, 2);
        }
        return number_format($amount, 2) . ' ' . $currency;
    }
    
    /**
     * Generate a unique transaction reference
     * 
     * @return string Unique reference
     */
    public static function generate_reference() {
        return 'sp-' . time() . '-' . wp_generate_password(8, false);
    }
    
    /**
     * Log API requests and responses for debugging
     * 
     * @param string $message The message to log
     * @param mixed $data Additional data to log
     */
    public static function log($message, $data = null) {
        if (WP_DEBUG && WP_DEBUG_LOG) {
            $log_entry = '[Stratos Pay] ' . $message;
            
            if ($data !== null) {
                $log_entry .= ' | Data: ' . print_r($data, true);
            }
            
            error_log($log_entry);
        }
    }
    
    /**
     * Generate webhook URL
     * 
     * @return string The webhook URL
     */
    public static function get_webhook_url() {
        return add_query_arg(
            'wc-api',
            'wc_stratos_pay_gateway',
            get_home_url('/', 'wc-api/stratos_pay/')
        );
    }
}