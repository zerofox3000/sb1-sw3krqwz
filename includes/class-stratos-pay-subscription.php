<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Subscription Payment Handler
 */
class Stratos_Pay_Subscription {
    /**
     * Initialize subscription features
     */
    public static function init() {
        add_action('woocommerce_scheduled_subscription_payment_stratos_pay', array(__CLASS__, 'process_subscription_payment'), 10, 2);
        add_action('woocommerce_subscription_status_cancelled', array(__CLASS__, 'cancel_subscription'));
    }
    
    /**
     * Process subscription payment
     */
    public static function process_subscription_payment($amount, $subscription) {
        $order = $subscription->get_parent();
        if (!$order) {
            return;
        }
        
        $payment_method = $order->get_payment_method();
        if ($payment_method !== 'stratos_pay') {
            return;
        }
        
        try {
            $gateway = new WC_Stratos_Pay_Gateway();
            
            // Get stored payment method
            $token = $subscription->get_payment_token();
            if (!$token) {
                throw new Exception(__('No stored payment method found', 'stratos-pay'));
            }
            
            // Process payment
            $result = $gateway->process_subscription_payment($subscription, $amount);
            
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }
            
            $subscription->payment_complete();
            
        } catch (Exception $e) {
            $subscription->payment_failed();
            Stratos_Pay_Logger::log('error', 'Subscription payment failed', array(
                'subscription_id' => $subscription->get_id(),
                'error' => $e->getMessage()
            ));
        }
    }
    
    /**
     * Cancel subscription
     */
    public static function cancel_subscription($subscription) {
        $gateway = new WC_Stratos_Pay_Gateway();
        
        try {
            $gateway->cancel_subscription($subscription);
        } catch (Exception $e) {
            Stratos_Pay_Logger::log('error', 'Failed to cancel subscription', array(
                'subscription_id' => $subscription->get_id(),
                'error' => $e->getMessage()
            ));
        }
    }
}