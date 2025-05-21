<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WooCommerce Stratos Pay Payment Gateway
 */
class WC_Stratos_Pay_Gateway extends WC_Payment_Gateway {
    /**
     * Constructor for the gateway.
     */
    public function __construct() {
        $this->id                 = 'stratos_pay';
        $this->icon               = apply_filters('woocommerce_stratos_pay_icon', STRATOS_PAY_PLUGIN_URL . 'assets/images/stratos-pay-logo.png');
        $this->has_fields         = true;
        $this->method_title       = __('Stratos Pay', 'stratos-pay');
        $this->method_description = __('Accept payments through Stratos Pay payment gateway.', 'stratos-pay');
        $this->supports           = array(
            'products',
            'refunds',
            'subscriptions',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation',
            'subscription_amount_changes',
            'subscription_date_changes',
            'subscription_payment_method_change',
            'subscription_payment_method_change_customer',
            'subscription_payment_method_change_admin',
            'multiple_subscriptions',
            'custom_order_tables'
        );
        
        // Load the settings
        $this->init_form_fields();
        $this->init_settings();
        
        // Define user set variables
        $this->title        = $this->get_option('title');
        $this->description  = $this->get_option('description');
        $this->enabled      = $this->get_option('enabled');
        $this->testmode     = 'yes' === $this->get_option('testmode');
        $this->webhook_secret = $this->get_option('webhook_secret');
        
        // Get API keys
        $this->test_public_key = $this->get_option('test_public_key');
        $this->test_secret_key = $this->get_option('test_secret_key');
        $this->public_key = $this->get_option('public_key');
        $this->secret_key = $this->get_option('secret_key');
        
        // Actions
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_api_wc_stratos_pay_gateway', array($this, 'webhook_handler'));
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
        
        // Add support for blocks
        add_action('woocommerce_blocks_loaded', array($this, 'register_blocks_support'));
    }

    /**
     * Register blocks support
     */
    public function register_blocks_support() {
        if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
            require_once STRATOS_PAY_PLUGIN_DIR . 'includes/blocks/class-wc-stratos-pay-blocks.php';
            add_action(
                'woocommerce_blocks_payment_method_type_registration',
                function(Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
                    $payment_method_registry->register(new WC_Stratos_Pay_Blocks_Support());
                }
            );
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
            'api_credentials' => array(
                'title'       => __('API Credentials', 'stratos-pay'),
                'type'        => 'title',
                'description' => __('Enter your Stratos Pay API credentials to process payments.', 'stratos-pay'),
            ),
            'testmode' => array(
                'title'       => __('Test mode', 'stratos-pay'),
                'label'       => __('Enable Test Mode', 'stratos-pay'),
                'type'        => 'checkbox',
                'description' => __('Place the payment gateway in test mode.', 'stratos-pay'),
                'default'     => 'yes',
                'desc_tip'    => true,
            ),
            'test_public_key' => array(
                'title'       => __('Test Public Key', 'stratos-pay'),
                'type'        => 'text',
                'description' => __('Enter your Test Public Key from Stratos Pay.', 'stratos-pay'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'test_secret_key' => array(
                'title'       => __('Test Secret Key', 'stratos-pay'),
                'type'        => 'password',
                'description' => __('Enter your Test Secret Key from Stratos Pay.', 'stratos-pay'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'public_key' => array(
                'title'       => __('Live Public Key', 'stratos-pay'),
                'type'        => 'text',
                'description' => __('Enter your Live Public Key from Stratos Pay.', 'stratos-pay'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'secret_key' => array(
                'title'       => __('Live Secret Key', 'stratos-pay'),
                'type'        => 'password',
                'description' => __('Enter your Live Secret Key from Stratos Pay.', 'stratos-pay'),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'webhook_settings' => array(
                'title'       => __('Webhook Settings', 'stratos-pay'),
                'type'        => 'title',
                'description' => sprintf(
                    __('To configure webhooks, add the following URL to your Stratos Pay dashboard webhook settings: %s', 'stratos-pay'),
                    '<code>' . Stratos_Pay_Helper::get_webhook_url() . '</code>'
                ),
            ),
            'webhook_secret' => array(
                'title'       => __('Webhook Secret', 'stratos-pay'),
                'type'        => 'password',
                'description' => __('Enter your Webhook Secret from Stratos Pay dashboard.', 'stratos-pay'),
                'default'     => '',
                'desc_tip'    => true,
            ),
        );
    }

    /**
     * Process the payment and return the result
     */
    public function process_payment($order_id) {
        try {
            $order = wc_get_order($order_id);
            
            // Create payment intent
            $response = wp_remote_post(Stratos_Pay_Helper::get_api_url() . '/payment_intents', array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->secret_key,
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode(array(
                    'amount' => $order->get_total() * 100, // Convert to cents
                    'currency' => $order->get_currency(),
                    'metadata' => array(
                        'order_id' => $order_id,
                        'customer_email' => $order->get_billing_email()
                    )
                ))
            ));
            
            if (is_wp_error($response)) {
                throw new Exception(__('Payment error:', 'stratos-pay') . ' ' . $response->get_error_message());
            }
            
            $result = json_decode(wp_remote_retrieve_body($response));
            
            if (!empty($result->error)) {
                throw new Exception(__('Payment error:', 'stratos-pay') . ' ' . $result->error->message);
            }
            
            // Store payment intent ID
            $order->update_meta_data('_stratos_pay_intent_id', $result->id);
            $order->save();
            
            return array(
                'result'   => 'success',
                'redirect' => $order->get_checkout_payment_url(true)
            );
            
        } catch (Exception $e) {
            wc_add_notice($e->getMessage(), 'error');
            return array(
                'result'   => 'fail',
                'redirect' => ''
            );
        }
    }

    /**
     * Process refund
     */
    public function process_refund($order_id, $amount = null, $reason = '') {
        try {
            $order = wc_get_order($order_id);
            
            if (!$order) {
                throw new Exception(__('Invalid order ID', 'stratos-pay'));
            }
            
            $intent_id = $order->get_meta('_stratos_pay_intent_id');
            
            if (!$intent_id) {
                throw new Exception(__('No payment intent found', 'stratos-pay'));
            }
            
            $response = wp_remote_post(Stratos_Pay_Helper::get_api_url() . '/refunds', array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->secret_key,
                    'Content-Type' => 'application/json'
                ),
                'body' => json_encode(array(
                    'payment_intent' => $intent_id,
                    'amount' => $amount * 100,
                    'reason' => $reason
                ))
            ));
            
            if (is_wp_error($response)) {
                throw new Exception($response->get_error_message());
            }
            
            $result = json_decode(wp_remote_retrieve_body($response));
            
            if (!empty($result->error)) {
                throw new Exception($result->error->message);
            }
            
            return true;
            
        } catch (Exception $e) {
            return new WP_Error('refund_error', $e->getMessage());
        }
    }

    /**
     * Payment form on checkout page
     */
    public function payment_fields() {
        if ($this->description) {
            echo wpautop(wp_kses_post($this->description));
        }
        ?>
        <div id="stratos-pay-payment-widget"></div>
        <button class="stratos-pay-payment-button">
            <?php esc_html_e('Pay with Stratos Pay', 'stratos-pay'); ?>
        </button>
        <?php
    }

    /**
     * Enqueue payment scripts
     */
    public function payment_scripts() {
        if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {
            return;
        }

        if ('no' === $this->enabled) {
            return;
        }

        wp_enqueue_script('stratos-pay-widget', 'https://pay.justwallet.com/payment/widget.js', array(), STRATOS_PAY_VERSION, true);
        wp_enqueue_script('wc-stratos-pay', STRATOS_PAY_PLUGIN_URL . 'assets/js/stratos-pay-checkout.js', array('jquery', 'stratos-pay-widget'), STRATOS_PAY_VERSION, true);
        wp_enqueue_script('wc-stratos-pay-express', STRATOS_PAY_PLUGIN_URL . 'assets/js/stratos-pay-express.js', array('jquery', 'stratos-pay-widget', '@woocommerce/blocks-registry'), STRATOS_PAY_VERSION, true);

        $order_id = get_query_var('order-pay');
        $order = wc_get_order($order_id);
        
        wp_localize_script('wc-stratos-pay', 'wc_stratos_pay_params', array(
            'public_key' => $this->public_key,
            'order_id' => $order ? $order->get_id() : null,
            'total' => $order ? $order->get_total() : WC()->cart->get_total(),
            'currency' => get_woocommerce_currency(),
            'billing_email' => $order ? $order->get_billing_email() : '',
            'billing_first_name' => $order ? $order->get_billing_first_name() : '',
            'billing_last_name' => $order ? $order->get_billing_last_name() : '',
            'billing_address_1' => $order ? $order->get_billing_address_1() : '',
            'billing_city' => $order ? $order->get_billing_city() : '',
            'billing_state' => $order ? $order->get_billing_state() : '',
            'billing_postcode' => $order ? $order->get_billing_postcode() : '',
            'billing_country' => $order ? $order->get_billing_country() : '',
            'return_url' => $this->get_return_url($order),
            'store_name' => get_bloginfo('name'),
            'store_logo' => get_site_icon_url()
        ));
    }

    /**
     * Handle webhooks
     */
    public function webhook_handler() {
        $payload = file_get_contents('php://input');
        $data    = json_decode($payload);

        if (!$data) {
            status_header(400);
            exit;
        }

        // Verify webhook signature
        $signature = $_SERVER['HTTP_STRATOS_SIGNATURE'] ?? '';
        
        if (!$this->verify_webhook_signature($payload, $signature, $this->webhook_secret)) {
            status_header(401);
            exit;
        }

        $order = wc_get_order($data->metadata->order_id);
        if (!$order) {
            status_header(404);
            exit;
        }

        // Process webhook data and update order status
        $this->process_webhook_status($order, $data);

        status_header(200);
        exit;
    }

    /**
     * Process webhook status
     */
    private function process_webhook_status($order, $data) {
        if ($data->status === 'succeeded') {
            $order->payment_complete($data->id);
            
            // Add transaction to our database
            Stratos_Pay_Transactions::add_transaction(array(
                'transaction_id' => $data->id,
                'amount' => $data->amount / 100,
                'currency' => $data->currency,
                'status' => 'completed',
                'customer_email' => $order->get_billing_email(),
                'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'metadata' => array(
                    'order_id' => $order->get_id()
                )
            ));
        } else if ($data->status === 'failed') {
            $order->update_status('failed', __('Payment failed', 'stratos-pay'));
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