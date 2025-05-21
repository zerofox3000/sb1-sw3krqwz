<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fraud Detection System
 */
class Stratos_Pay_Fraud_Detection {
    private static $risk_threshold = 0.8;
    
    /**
     * Check transaction risk
     */
    public static function check_risk($order, $payment_data) {
        $risk_factors = array();
        $risk_score = 0;
        
        // Check IP location
        $ip_risk = self::check_ip_risk($payment_data['ip_address']);
        if ($ip_risk > 0) {
            $risk_factors['ip_risk'] = $ip_risk;
            $risk_score += $ip_risk;
        }
        
        // Check order velocity
        $velocity_risk = self::check_order_velocity($order->get_billing_email());
        if ($velocity_risk > 0) {
            $risk_factors['velocity_risk'] = $velocity_risk;
            $risk_score += $velocity_risk;
        }
        
        // Check amount threshold
        $amount_risk = self::check_amount_threshold($order->get_total());
        if ($amount_risk > 0) {
            $risk_factors['amount_risk'] = $amount_risk;
            $risk_score += $amount_risk;
        }
        
        // Check device fingerprint
        $device_risk = self::check_device_fingerprint($payment_data['device_id']);
        if ($device_risk > 0) {
            $risk_factors['device_risk'] = $device_risk;
            $risk_score += $device_risk;
        }
        
        $final_score = $risk_score / count($risk_factors);
        
        // Log risk assessment
        Stratos_Pay_Logger::log('info', 'Fraud risk assessment', array(
            'order_id' => $order->get_id(),
            'risk_factors' => $risk_factors,
            'final_score' => $final_score
        ));
        
        return array(
            'is_risky' => $final_score >= self::$risk_threshold,
            'risk_score' => $final_score,
            'risk_factors' => $risk_factors
        );
    }
    
    /**
     * Check IP address risk
     */
    private static function check_ip_risk($ip) {
        // Check if IP is from high-risk country
        $high_risk_countries = array('XX', 'YY', 'ZZ'); // Replace with actual high-risk country codes
        
        $reader = new \MaxMind\Db\Reader(STRATOS_PAY_PLUGIN_DIR . 'data/GeoLite2-Country.mmdb');
        $record = $reader->get($ip);
        
        if ($record && in_array($record['country']['iso_code'], $high_risk_countries)) {
            return 0.9;
        }
        
        return 0;
    }
    
    /**
     * Check order velocity
     */
    private static function check_order_velocity($email) {
        global $wpdb;
        
        // Check number of orders in last hour
        $last_hour_orders = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wc_orders 
            WHERE billing_email = %s 
            AND date_created >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            $email
        ));
        
        if ($last_hour_orders > 5) {
            return 0.8;
        }
        
        return 0;
    }
    
    /**
     * Check amount threshold
     */
    private static function check_amount_threshold($amount) {
        // Flag high-value transactions
        if ($amount > 1000) {
            return 0.6;
        }
        
        return 0;
    }
    
    /**
     * Check device fingerprint
     */
    private static function check_device_fingerprint($device_id) {
        global $wpdb;
        
        // Check if device has been used with multiple emails
        $email_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT billing_email) 
            FROM {$wpdb->prefix}stratos_pay_devices 
            WHERE device_id = %s",
            $device_id
        ));
        
        if ($email_count > 3) {
            return 0.7;
        }
        
        return 0;
    }
}