<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Stratos Pay Payment Gateway
 */
class WC_Stratos_Pay_Gateway extends WC_Payment_Gateway {
    /**
     * Debug mode
     */
    public $debug;

    /**
     * Constructor for the gateway.
     */
    public function __construct() {
        $this->id                 = 'stratos_pay';
        $this->has_fields         = true;
        $this->method_title       = __('Stratos Pay', 'stratos-pay');
        $this->method_description = __('Accept payments through Stratos Pay payment gateway.', 'stratos-pay');
        $this->supports           = array(
            'products',
            'refunds'
        );
        
        // Load the settings
        $this->init_form_fields();
        $this->init_settings();
        
        // Define user set variables
        $this->title        = $this->get_option('title');
        $this->description  = $this->get_option('description');
        $this->enabled      = $this->get_option('enabled');
        $this->testmode     = 'yes' === $this->get_option('testmode');
        $this->debug        = 'yes' === $this->get_option('debug');
        
        // Get API keys
        $settings = get_option('stratos_pay_settings');
        $this->public_key = $this->testmode ? $settings['test_public_key'] : $settings['public_key'];
        $this->secret_key = $this->testmode ? $settings['test_secret_key'] : $settings['secret_key'];
        
        // Actions
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_api_wc_stratos_pay_gateway', array($this, 'webhook_handler'));
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
        
        // Debug log
        if ($this->debug) {
            if (class_exists('WC_Logger')) {
                $this->log = new WC_Logger();
            }
        }
    }

    /**
     * Debug log
     */
    public function log($message, $level = 'info') {
        if ($this->debug) {
            if (empty($this->log)) {
                $this->log = new WC_Logger();
            }
            $this->log->add('stratos_pay', $message);
        }
    }

    /**
     * Initialize Gateway Settings Form Fields
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __('Enable/Disable', 'stratos-pay'),
                'type'    => 'checkbox',
                'label'   => __('Enable Stratos Pay', 'stratos-pay'),
                'default' => 'yes'
            ),
            'title' => array(
                'title'       => __('Title', 'stratos-pay'),
                'type'        => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'stratos-pay'),
                'default'     => __('Stratos Pay', 'stratos-pay'),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __('Description', 'stratos-pay'),
                'type'        => 'textarea',
                'description' => __('Payment method description that the customer will see on your checkout.', 'stratos-pay'),
                'default'     => __('Pay securely using Stratos Pay.', 'stratos-pay'),
                'desc_tip'    => true,
            ),
            'testmode' => array(
                'title'       => __('Test mode', 'stratos-pay'),
                'label'       => __('Enable Test Mode', 'stratos-pay'),
                'type'        => 'checkbox',
                'description' => __('Place the payment gateway in test mode.', 'stratos-pay'),
                'default'     => 'yes',
                'desc_tip'    => true,
            ),
            'debug' => array(
                'title'       => __('Debug log', 'stratos-pay'),
                'type'        => 'checkbox',
                'label'       => __('Enable logging', 'stratos-pay'),
                'default'     => 'no',
                'description' => __('Log Stratos Pay events inside <code>woocommerce/logs/stratos-pay.txt</code>', 'stratos-pay'),
            ),
        );
    }

    /**
     * Process the payment and return the result
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        
        try {
            $this->log("Processing payment for order " . $order_id);

            // Generate unique reference
            $reference = 'order_' . $order_id . '_' . time();

            // Store reference
            $order->update_meta_data('_stratos_pay_reference', $reference);
            $order->save();

            // Return parameters for popup
            return array(
                'result' => 'success',
                'stratos_pay_params' => array(
                    'public_key' => $this->public_key,
                    'external_reference' => $reference,
                    'amount' => $order->get_total() * 100, // Convert to cents
                    'currency' => $order->get_currency(),
                    'callback_url' => $this->get_return_url($order),
                    'customer' => array(
                        'email' => $order->get_billing_email(),
                        'first_name' => $order->get_billing_first_name(),
                        'last_name' => $order->get_billing_last_name(),
                        'ip_address' => WC_Geolocation::get_ip_address()
                    ),
                    'billing_address' => array(
                        'country' => $order->get_billing_country(),
                        'state' => $order->get_billing_state(),
                        'city' => $order->get_billing_city(),
                        'address' => $order->get_billing_address_1(),
                        'postal_code' => $order->get_billing_postcode()
                    ),
                    'title' => get_bloginfo('name'),
                    'description' => 'Order #' . $order->get_order_number()
                )
            );
            
        } catch (Exception $e) {
            $this->log("Payment error: " . $e->getMessage(), 'error');
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Payment form on checkout page
     */
    public function payment_fields() {
        if ($this->description) {
            echo wpautop(wp_kses_post($this->description));
        }
    }

    /**
     * Enqueue payment scripts
     */
    public function payment_scripts() {
        if (!is_checkout()) {
            return;
        }

        if ('no' === $this->enabled) {
            return;
        }

        wp_enqueue_script('stratos-pay-popup', 'https://stratospay.com/popup.js', array(), STRATOS_PAY_VERSION, true);
        wp_enqueue_script('stratos-pay-checkout', STRATOS_PAY_PLUGIN_URL . 'assets/js/stratos-pay-checkout.js', array('jquery', 'stratos-pay-popup'), STRATOS_PAY_VERSION, true);
    }

    /**
     * Handle webhooks
     */
    public function webhook_handler() {
        $this->log("Received webhook");

        $payload = file_get_contents('php://input');
        $data = json_decode($payload);

        $this->log("Webhook payload: " . print_r($data, true));

        if (!$data) {
            $this->log("Invalid webhook payload", 'error');
            status_header(400);
            exit;
        }

        // Verify webhook signature
        $signature = isset($_SERVER['HTTP_STRATOS_SIGNATURE']) ? $_SERVER['HTTP_STRATOS_SIGNATURE'] : '';
        $settings = get_option('stratos_pay_settings');
        
        if (!$this->verify_webhook_signature($payload, $signature, $settings['webhook_secret'])) {
            $this->log("Invalid webhook signature", 'error');
            status_header(401);
            exit;
        }

        // Get order from reference
        $reference = $data->metadata->external_reference ?? '';
        $order_id = intval(substr($reference, 6));
        $order = wc_get_order($order_id);

        if (!$order) {
            $this->log("Order not found: " . $order_id, 'error');
            status_header(404);
            exit;
        }

        $this->log("Processing webhook for order " . $order_id);

        // Process webhook data
        switch ($data->type) {
            case 'payment.succeeded':
                $order->payment_complete($data->id);
                $order->add_order_note(__('Payment completed via Stratos Pay', 'stratos-pay'));
                
                $this->log("Payment successful for order " . $order_id);
                
                // Store transaction
                Stratos_Pay_Transactions::add_transaction(array(
                    'transaction_id' => $data->id,
                    'amount' => $data->amount / 100,
                    'currency' => $data->currency,
                    'status' => 'completed',
                    'customer_email' => $order->get_billing_email(),
                    'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                    'type' => $data->payment_method_type,
                    'metadata' => array(
                        'order_id' => $order_id
                    )
                ));
                break;

            case 'payment.failed':
                $error_message = $data->error->message ?? 'Payment failed';
                $order->update_status('failed', $error_message);
                
                $this->log("Payment failed for order " . $order_id . ": " . $error_message, 'error');
                
                // Store failed transaction
                Stratos_Pay_Transactions::add_transaction(array(
                    'transaction_id' => $data->id,
                    'amount' => $data->amount / 100,
                    'currency' => $data->currency,
                    'status' => 'failed',
                    'customer_email' => $order->get_billing_email(),
                    'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                    'type' => $data->payment_method_type,
                    'metadata' => array(
                        'order_id' => $order_id,
                        'error' => $error_message
                    )
                ));
                break;
        }

        status_header(200);
        exit;
    }

    /**
     * Process refund
     */
    public function process_refund($order_id, $amount = null, $reason = '') {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return new WP_Error('invalid_order', __('Invalid order ID', 'stratos-pay'));
        }
        
        try {
            $this->log("Processing refund for order " . $order_id);

            $transaction_id = $order->get_transaction_id();
            if (!$transaction_id) {
                throw new Exception(__('No transaction ID found', 'stratos-pay'));
            }
            
            $response = wp_remote_post(Stratos_Pay_Helper::get_api_url() . '/refunds', array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->secret_key,
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode(array(
                    'payment' => $transaction_id,
                    'amount' => round($amount * 100),
                    'reason' => $reason,
                    'metadata' => array(
                        'order_id' => $order_id
                    )
                ))
            ));
            
            $this->log("Refund response: " . print_r($response, true));

            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }
            
            $result = json_decode(wp_remote_retrieve_body($response));
            
            if (!empty($result->error)) {
                throw new Exception($result->error->message);
            }
            
            // Add refund note
            $order->add_order_note(
                sprintf(__('Refunded %s - Refund ID: %s', 'stratos-pay'),
                    wc_price($amount),
                    $result->id
                )
            );
            
            $this->log("Refund successful for order " . $order_id);
            return true;
            
        } catch (Exception $e) {
            $this->log("Refund failed for order " . $order_id . ": " . $e->getMessage(), 'error');
            return new WP_Error('refund_error', $e->getMessage());
        }
    }

    /**
     * Verify webhook signature
     */
    private function verify_webhook_signature($payload, $signature, $secret) {
        if (empty($signature) || empty($secret)) {
            return false;
        }
        
        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }
}