<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get plugin settings
$settings = get_option('stratos_pay_settings', array(
    'test_public_key' => '',
    'test_secret_key' => '',
    'public_key' => '',
    'secret_key' => '',
    'webhook_secret' => '',
    'environment' => 'sandbox'
));

// Generate webhook URL
$webhook_url = add_query_arg(
    'wc-api',
    'wc_stratos_pay_gateway',
    get_home_url('/', 'wc-api/stratos_pay/')
);
?>
<div class="wrap stratos-pay-wrap">
    <h1 class="stratos-pay-title"><?php _e('Stratos Pay Settings', 'stratos-pay'); ?></h1>
    
    <div class="stratos-pay-container">
        <div class="stratos-pay-grid">
            <!-- API Configuration Box -->
            <div class="stratos-pay-box">
                <h2><?php _e('API Configuration', 'stratos-pay'); ?></h2>
                <form method="post" action="options.php" class="stratos-pay-settings-form">
                    <?php settings_fields('stratos_pay_settings'); ?>
                    
                    <!-- Test Mode Keys -->
                    <div class="stratos-pay-form-row">
                        <label for="stratos_pay_test_public_key">
                            <?php _e('Test Public Key', 'stratos-pay'); ?>
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="stratos_pay_test_public_key" 
                            name="stratos_pay_settings[test_public_key]" 
                            value="<?php echo esc_attr($settings['test_public_key']); ?>" 
                            placeholder="<?php _e('Enter your Test Public Key', 'stratos-pay'); ?>"
                        />
                    </div>
                    
                    <div class="stratos-pay-form-row">
                        <label for="stratos_pay_test_secret_key">
                            <?php _e('Test Secret Key', 'stratos-pay'); ?>
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="stratos_pay_test_secret_key" 
                            name="stratos_pay_settings[test_secret_key]" 
                            value="<?php echo esc_attr($settings['test_secret_key']); ?>" 
                            placeholder="<?php _e('Enter your Test Secret Key', 'stratos-pay'); ?>"
                        />
                    </div>
                    
                    <!-- Live Mode Keys -->
                    <div class="stratos-pay-form-row">
                        <label for="stratos_pay_public_key">
                            <?php _e('Live Public Key', 'stratos-pay'); ?>
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="stratos_pay_public_key" 
                            name="stratos_pay_settings[public_key]" 
                            value="<?php echo esc_attr($settings['public_key']); ?>" 
                            placeholder="<?php _e('Enter your Live Public Key', 'stratos-pay'); ?>"
                        />
                    </div>
                    
                    <div class="stratos-pay-form-row">
                        <label for="stratos_pay_secret_key">
                            <?php _e('Live Secret Key', 'stratos-pay'); ?>
                            <span class="required">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="stratos_pay_secret_key" 
                            name="stratos_pay_settings[secret_key]" 
                            value="<?php echo esc_attr($settings['secret_key']); ?>" 
                            placeholder="<?php _e('Enter your Live Secret Key', 'stratos-pay'); ?>"
                        />
                    </div>
                    
                    <div class="stratos-pay-form-row">
                        <label for="stratos_pay_environment">
                            <?php _e('Environment', 'stratos-pay'); ?>
                        </label>
                        <select id="stratos_pay_environment" name="stratos_pay_settings[environment]">
                            <option value="sandbox" <?php selected($settings['environment'], 'sandbox'); ?>>
                                <?php _e('Sandbox (Testing)', 'stratos-pay'); ?>
                            </option>
                            <option value="production" <?php selected($settings['environment'], 'production'); ?>>
                                <?php _e('Production (Live)', 'stratos-pay'); ?>
                            </option>
                        </select>
                    </div>
                    
                    <div class="stratos-pay-form-submit">
                        <?php submit_button(__('Save Settings', 'stratos-pay'), 'stratos-pay-button', 'submit', false); ?>
                    </div>
                </form>
            </div>

            <!-- Webhook Configuration Box -->
            <div class="stratos-pay-box">
                <h2><?php _e('Webhook Configuration', 'stratos-pay'); ?></h2>
                <div class="stratos-pay-form-row">
                    <label for="stratos_pay_webhook_url">
                        <?php _e('Webhook URL', 'stratos-pay'); ?>
                    </label>
                    <input 
                        type="text" 
                        id="stratos_pay_webhook_url" 
                        value="<?php echo esc_url($webhook_url); ?>" 
                        readonly
                    />
                    <p class="description">
                        <?php _e('Add this URL to your Stratos Pay dashboard webhook settings.', 'stratos-pay'); ?>
                    </p>
                </div>
                
                <div class="stratos-pay-form-row">
                    <label for="stratos_pay_webhook_secret">
                        <?php _e('Webhook Secret', 'stratos-pay'); ?>
                        <span class="required">*</span>
                    </label>
                    <input 
                        type="password" 
                        id="stratos_pay_webhook_secret" 
                        name="stratos_pay_settings[webhook_secret]" 
                        value="<?php echo esc_attr($settings['webhook_secret']); ?>" 
                        placeholder="<?php _e('Enter your Webhook Secret', 'stratos-pay'); ?>"
                    />
                </div>
            </div>

            <!-- Support Box -->
            <div class="stratos-pay-box">
                <h2><?php _e('Need Help?', 'stratos-pay'); ?></h2>
                <p><?php _e('Get support from our team:', 'stratos-pay'); ?></p>
                <div class="stratos-pay-support-links">
                    <a href="https://docs.stratospay.com" target="_blank" class="stratos-pay-button">
                        <?php _e('Documentation', 'stratos-pay'); ?>
                    </a>
                    <a href="mailto:support@stratospay.com" class="stratos-pay-button">
                        <?php _e('Email Support', 'stratos-pay'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>