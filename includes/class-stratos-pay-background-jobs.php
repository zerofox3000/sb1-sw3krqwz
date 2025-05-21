<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Background Jobs Handler
 */
class Stratos_Pay_Background_Jobs {
    /**
     * Schedule a background job
     */
    public static function schedule_job($hook, $args = array(), $timestamp = 0) {
        if (!$timestamp) {
            $timestamp = time();
        }
        
        wp_schedule_single_event($timestamp, $hook, array($args));
    }
    
    /**
     * Process webhook asynchronously
     */
    public static function process_webhook($payload) {
        self::schedule_job('stratos_pay_process_webhook', array(
            'payload' => $payload,
            'timestamp' => time()
        ));
    }
    
    /**
     * Process refund asynchronously
     */
    public static function process_refund($order_id, $amount, $reason) {
        self::schedule_job('stratos_pay_process_refund', array(
            'order_id' => $order_id,
            'amount' => $amount,
            'reason' => $reason,
            'timestamp' => time()
        ));
    }
}