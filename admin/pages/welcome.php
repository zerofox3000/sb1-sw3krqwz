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
    <div class="stratos-pay-container">
        <h1 class="stratos-pay-title">Welcome to Stratos Pay</h1>
        
        <div class="stratos-pay-grid">
            <!-- Get Started Box -->
            <div class="stratos-pay-box">
                <h2>Need an Account?</h2>
                <p>We onboard all verticals of ecommerce, low risk and high risk. Apply now to get started Today.</p>
                <a href="https://stratospay.com/signup" target="_blank" class="stratos-pay-button">
                    Get Started Now
                </a>
            </div>

            <!-- Trust Module Box -->
            <div class="stratos-pay-box">
                <h2>Licensed Provider</h2>
                <p>Stratos Pay is a licensed payment service provider that provides the best cost options for all merchant types.</p>
                <ul class="stratos-pay-list">
                    <li>Research Peptide Sales</li>
                    <li>Gaming</li>
                    <li>Crypto</li>
                    <li>Ecommerce</li>
                </ul>
            </div>

            <!-- Settings Box -->
            <div class="stratos-pay-box">
                <h2>Quick Setup</h2>
                <p>Once you have your account, load your API settings in straight away.</p>
                <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=stratos_pay'); ?>" class="stratos-pay-button">
                    Configure Settings
                </a>
            </div>
        </div>
    </div>
</div>