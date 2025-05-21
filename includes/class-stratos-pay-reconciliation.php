<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Automated Reconciliation System
 */
class Stratos_Pay_Reconciliation {
    /**
     * Initialize reconciliation
     */
    public static function init() {
        // Schedule daily reconciliation
        if (!wp_next_scheduled('stratos_pay_daily_reconciliation')) {
            wp_schedule_event(time(), 'daily', 'stratos_pay_daily_reconciliation');
        }
        
        add_action('stratos_pay_daily_reconciliation', array(__CLASS__, 'run_reconciliation'));
    }
    
    /**
     * Run reconciliation process
     */
    public static function run_reconciliation() {
        $gateway = new WC_Stratos_Pay_Gateway();
        $settings = get_option('stratos_pay_settings');
        
        try {
            // Get transactions from Stratos Pay API
            $response = wp_remote_get(
                Stratos_Pay_Helper::get_api_url() . '/transactions',
                array(
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $gateway->secret_key
                    )
                )
            );
            
            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }
            
            $transactions = json_decode(wp_remote_retrieve_body($response));
            
            foreach ($transactions as $transaction) {
                self::reconcile_transaction($transaction);
            }
            
            Stratos_Pay_Logger::log('info', 'Reconciliation completed', array(
                'transactions_processed' => count($transactions)
            ));
            
        } catch (Exception $e) {
            Stratos_Pay_Logger::log('error', 'Reconciliation failed', array(
                'error' => $e->getMessage()
            ));
        }
    }
    
    /**
     * Reconcile individual transaction
     */
    private static function reconcile_transaction($transaction) {
        global $wpdb;
        
        // Check if transaction exists in our database
        $local_transaction = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}stratos_pay_transactions 
            WHERE transaction_id = %s",
            $transaction->id
        ));
        
        if (!$local_transaction) {
            // Transaction missing from our database
            Stratos_Pay_Logger::log('warning', 'Missing transaction found during reconciliation', array(
                'transaction_id' => $transaction->id
            ));
            
            // Add transaction to our database
            Stratos_Pay_Transactions::add_transaction(array(
                'transaction_id' => $transaction->id,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'status' => $transaction->status,
                'customer_email' => $transaction->customer_email,
                'customer_name' => $transaction->customer_name,
                'metadata' => $transaction->metadata
            ));
        } else {
            // Check for discrepancies
            if ($local_transaction->amount != $transaction->amount ||
                $local_transaction->status != $transaction->status) {
                
                Stratos_Pay_Logger::log('warning', 'Transaction discrepancy found', array(
                    'transaction_id' => $transaction->id,
                    'local_data' => array(
                        'amount' => $local_transaction->amount,
                        'status' => $local_transaction->status
                    ),
                    'remote_data' => array(
                        'amount' => $transaction->amount,
                        'status' => $transaction->status
                    )
                ));
                
                // Update local transaction
                Stratos_Pay_Transactions::update_transaction($transaction->id, array(
                    'amount' => $transaction->amount,
                    'status' => $transaction->status
                ));
            }
        }
    }
}