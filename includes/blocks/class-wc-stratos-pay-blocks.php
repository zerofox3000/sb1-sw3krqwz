<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Stratos Pay Blocks integration
 */
class WC_Stratos_Pay_Blocks_Support extends AbstractPaymentMethodType {
    /**
     * Payment method name/id/slug.
     *
     * @var string
     */
    protected $name = 'stratos_pay';

    /**
     * Initializes the payment method type.
     */
    public function initialize() {
        $this->settings = get_option('woocommerce_stratos_pay_settings', array());
    }

    /**
     * Returns if this payment method should be active. If false, the scripts will not be enqueued.
     *
     * @return boolean
     */
    public function is_active() {
        return true;
    }

    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     *
     * @return array
     */
    public function get_payment_method_script_handles() {
        wp_register_script(
            'wc-stratos-pay-blocks',
            STRATOS_PAY_PLUGIN_URL . 'assets/js/stratos-pay-express.js',
            array('wc-blocks-registry', 'wc-settings', 'wp-element'),
            STRATOS_PAY_VERSION,
            true
        );
        return array('wc-stratos-pay-blocks');
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data() {
        return array(
            'title' => $this->get_setting('title'),
            'description' => $this->get_setting('description'),
            'supports' => $this->get_supported_features(),
            'public_key' => $this->get_public_key(),
        );
    }

    /**
     * Get public key
     */
    private function get_public_key() {
        $settings = get_option('stratos_pay_settings');
        $testmode = isset($this->settings['testmode']) && $this->settings['testmode'] === 'yes';
        return $testmode ? $settings['test_public_key'] : $settings['public_key'];
    }
}