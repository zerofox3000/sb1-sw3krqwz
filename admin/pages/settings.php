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

// Function to mask API keys
function mask_key($key) {
    if (empty($key)) return '';
    return str_repeat('•', strlen($key));
}
?>
<div class="wrap stratos-pay-wrap">
    <h1 class="stratos-pay-title"><?php _e('Stratos Pay Settings', 'stratos-pay'); ?></h1>
    
    <div class="stratos-pay-container">
        <form method="post" action="options.php" class="stratos-pay-settings-form">
            <?php settings_fields('stratos_pay_settings'); ?>
            
            <div class="stratos-pay-box">
                <h2><?php _e('API Keys', 'stratos-pay'); ?></h2>
                
                <div class="stratos-pay-key-group">
                    <h3><?php _e('Live Key', 'stratos-pay'); ?></h3>
                    <div class="stratos-pay-key-field">
                        <input 
                            type="password" 
                            name="stratos_pay_settings[secret_key]" 
                            value="<?php echo esc_attr($settings['secret_key']); ?>" 
                            placeholder="<?php echo !empty($settings['secret_key']) ? mask_key($settings['secret_key']) : __('Enter your Live Key', 'stratos-pay'); ?>"
                        />
                        <button type="button" class="toggle-visibility">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                    </div>
                </div>

                <div class="stratos-pay-key-group">
                    <h3><?php _e('Test Key', 'stratos-pay'); ?></h3>
                    <div class="stratos-pay-key-field">
                        <input 
                            type="password" 
                            name="stratos_pay_settings[test_secret_key]" 
                            value="<?php echo esc_attr($settings['test_secret_key']); ?>" 
                            placeholder="<?php echo !empty($settings['test_secret_key']) ? mask_key($settings['test_secret_key']) : __('Enter your Test Key', 'stratos-pay'); ?>"
                        />
                        <button type="button" class="toggle-visibility">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="stratos-pay-box">
                <h2><?php _e('Public Keys', 'stratos-pay'); ?></h2>
                
                <div class="stratos-pay-key-group">
                    <h3><?php _e('Live Key', 'stratos-pay'); ?></h3>
                    <div class="stratos-pay-key-field">
                        <input 
                            type="password" 
                            name="stratos_pay_settings[public_key]" 
                            value="<?php echo esc_attr($settings['public_key']); ?>" 
                            placeholder="<?php echo !empty($settings['public_key']) ? mask_key($settings['public_key']) : __('Enter your Live Public Key', 'stratos-pay'); ?>"
                        />
                        <button type="button" class="toggle-visibility">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                    </div>
                </div>

                <div class="stratos-pay-key-group">
                    <h3><?php _e('Test Key', 'stratos-pay'); ?></h3>
                    <div class="stratos-pay-key-field">
                        <input 
                            type="password" 
                            name="stratos_pay_settings[test_public_key]" 
                            value="<?php echo esc_attr($settings['test_public_key']); ?>" 
                            placeholder="<?php echo !empty($settings['test_public_key']) ? mask_key($settings['test_public_key']) : __('Enter your Test Public Key', 'stratos-pay'); ?>"
                        />
                        <button type="button" class="toggle-visibility">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="stratos-pay-box">
                <h2><?php _e('Webhook & IP Whitelisting', 'stratos-pay'); ?></h2>
                
                <div class="stratos-pay-key-group">
                    <h3><?php _e('IP Whitelisted', 'stratos-pay'); ?></h3>
                    <div class="stratos-pay-key-field">
                        <input type="text" value="<?php echo esc_attr($_SERVER['SERVER_ADDR']); ?>" readonly />
                    </div>
                </div>

                <div class="stratos-pay-key-group">
                    <h3><?php _e('Webhook URL', 'stratos-pay'); ?></h3>
                    <div class="stratos-pay-key-field">
                        <input type="text" value="<?php echo esc_url($webhook_url); ?>" readonly />
                        <button type="button" class="copy-to-clipboard">
                            <span class="dashicons dashicons-clipboard"></span>
                        </button>
                    </div>
                </div>

                <div class="stratos-pay-key-group">
                    <h3><?php _e('Webhook Secret', 'stratos-pay'); ?></h3>
                    <div class="stratos-pay-key-field">
                        <input 
                            type="password" 
                            name="stratos_pay_settings[webhook_secret]" 
                            value="<?php echo esc_attr($settings['webhook_secret']); ?>" 
                            placeholder="<?php echo !empty($settings['webhook_secret']) ? mask_key($settings['webhook_secret']) : __('Enter your Webhook Secret', 'stratos-pay'); ?>"
                        />
                        <button type="button" class="toggle-visibility">
                            <span class="dashicons dashicons-visibility"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="stratos-pay-form-submit">
                <?php submit_button(__('Save Settings', 'stratos-pay'), 'stratos-pay-button', 'submit', false); ?>
            </div>
        </form>
    </div>
</div>

<style>
.stratos-pay-key-group {
    margin-bottom: 1.5rem;
}

.stratos-pay-key-group h3 {
    color: var(--stratos-text);
    font-size: 1rem;
    margin: 0 0 0.5rem 0;
}

.stratos-pay-key-field {
    position: relative;
    display: flex;
    align-items: center;
}

.stratos-pay-key-field input {
    flex: 1;
    padding: 0.75rem;
    padding-right: 40px;
    border-radius: 0.5rem;
    border: 1px solid var(--stratos-border);
    background: rgba(255, 255, 255, 0.05);
    color: var(--stratos-text);
    width: 100%;
}

.stratos-pay-key-field input:focus {
    outline: none;
    border-color: var(--stratos-blue);
    box-shadow: 0 0 0 2px rgba(0, 180, 216, 0.2);
}

.stratos-pay-key-field button {
    position: absolute;
    right: 8px;
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    color: var(--stratos-text-muted);
}

.stratos-pay-key-field button:hover {
    color: var(--stratos-teal);
}

.stratos-pay-key-field .dashicons {
    width: 20px;
    height: 20px;
    font-size: 20px;
}

.stratos-pay-form-submit {
    margin-top: 2rem;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.toggle-visibility').on('click', function() {
        const input = $(this).parent().find('input');
        const icon = $(this).find('.dashicons');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
        } else {
            input.attr('type', 'password');
            icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
        }
    });

    $('.copy-to-clipboard').on('click', function() {
        const input = $(this).parent().find('input');
        input.select();
        document.execCommand('copy');
        
        const icon = $(this).find('.dashicons');
        icon.removeClass('dashicons-clipboard').addClass('dashicons-yes');
        setTimeout(() => {
            icon.removeClass('dashicons-yes').addClass('dashicons-clipboard');
        }, 1000);
    });
});
</script>