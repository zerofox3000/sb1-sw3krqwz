<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get plugin settings
$settings = get_option('stratos_pay_settings', array(
    'api_key' => '',
    'environment' => 'sandbox'
));
?>
<div class="wrap stratos-pay-wrap">
    <h1 class="stratos-pay-title">Welcome to Stratos Pay</h1>
    
    <div class="stratos-pay-container">
        <div class="stratos-pay-grid">
            <!-- Get Started Box -->
            <div class="stratos-pay-box">
                <h2><?php _e('Get Started', 'stratos-pay'); ?></h2>
                <p><?php _e('We onboard all verticals of ecommerce, low risk and high risk. Apply now to get started Today.', 'stratos-pay'); ?></p>
                <div class="stratos-pay-features">
                    <div class="stratos-pay-feature">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php _e('Research Peptide Sales', 'stratos-pay'); ?>
                    </div>
                    <div class="stratos-pay-feature">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php _e('Gaming', 'stratos-pay'); ?>
                    </div>
                    <div class="stratos-pay-feature">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php _e('Crypto', 'stratos-pay'); ?>
                    </div>
                    <div class="stratos-pay-feature">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <?php _e('Ecommerce', 'stratos-pay'); ?>
                    </div>
                </div>
                <a href="https://stratospay.com/signup" target="_blank" class="stratos-pay-button">
                    <?php _e('Get Started Now', 'stratos-pay'); ?>
                </a>
            </div>

            <!-- Quick Setup Box -->
            <div class="stratos-pay-box">
                <h2><?php _e('Quick Setup', 'stratos-pay'); ?></h2>
                <p><?php _e('Configure your payment gateway settings to start accepting payments.', 'stratos-pay'); ?></p>
                <div class="stratos-pay-setup-steps">
                    <div class="stratos-pay-step">
                        <span class="step-number">1</span>
                        <?php _e('Create your account', 'stratos-pay'); ?>
                    </div>
                    <div class="stratos-pay-step">
                        <span class="step-number">2</span>
                        <?php _e('Get your API keys', 'stratos-pay'); ?>
                    </div>
                    <div class="stratos-pay-step">
                        <span class="step-number">3</span>
                        <?php _e('Configure settings', 'stratos-pay'); ?>
                    </div>
                </div>
                <a href="<?php echo admin_url('admin.php?page=stratos-pay-settings'); ?>" class="stratos-pay-button">
                    <?php _e('Configure Settings', 'stratos-pay'); ?>
                </a>
            </div>

            <!-- Support Box -->
            <div class="stratos-pay-box">
                <h2><?php _e('Need Help?', 'stratos-pay'); ?></h2>
                <p><?php _e('Get support from our expert team anytime.', 'stratos-pay'); ?></p>
                <div class="stratos-pay-support-options">
                    <a href="https://docs.stratospay.com" target="_blank" class="stratos-pay-support-link">
                        <span class="dashicons dashicons-book"></span>
                        <span class="support-text">
                            <strong><?php _e('Documentation', 'stratos-pay'); ?></strong>
                            <small><?php _e('Browse our detailed guides', 'stratos-pay'); ?></small>
                        </span>
                    </a>
                    <a href="mailto:support@stratospay.com" class="stratos-pay-support-link">
                        <span class="dashicons dashicons-email"></span>
                        <span class="support-text">
                            <strong><?php _e('Email Support', 'stratos-pay'); ?></strong>
                            <small><?php _e('Get help from our team', 'stratos-pay'); ?></small>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>