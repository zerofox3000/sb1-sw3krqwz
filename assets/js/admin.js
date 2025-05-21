/**
 * Stratos Pay Admin JavaScript
 */
(function($) {
    'use strict';
    
    // Document ready
    $(document).ready(function() {
        // Initialize Stratos Pay admin
        initStratos();
    });
    
    /**
     * Initialize Stratos Pay admin functionality
     */
    function initStratos() {
        // Settings form handling
        handleSettingsForm();
        
        // Add smooth animations
        initAnimations();
    }
    
    /**
     * Handle settings form submission
     */
    function handleSettingsForm() {
        var $form = $('.stratos-pay-settings-form');
        
        if ($form.length) {
            $form.on('submit', function(e) {
                // Add loading state
                var $submitButton = $form.find('.button-primary');
                $submitButton.addClass('updating-message').prop('disabled', true);
                
                // This is just for UX, the actual saving is handled by WordPress
                setTimeout(function() {
                    $submitButton.removeClass('updating-message').prop('disabled', false);
                }, 1000);
            });
        }
    }
    
    /**
     * Initialize animations for UI elements
     */
    function initAnimations() {
        // Add subtle hover effects to cards and buttons
        $('.stratos-pay-benefits, .stratos-pay-setup, .stratos-pay-support-box').hover(
            function() {
                $(this).css({
                    'transform': 'translateY(-2px)',
                    'box-shadow': '0 4px 8px rgba(0, 0, 0, 0.15)',
                    'transition': 'all 0.3s ease'
                });
            },
            function() {
                $(this).css({
                    'transform': 'translateY(0)',
                    'box-shadow': '0 1px 3px rgba(0, 0, 0, 0.12)',
                    'transition': 'all 0.3s ease'
                });
            }
        );
    }
    
})(jQuery);