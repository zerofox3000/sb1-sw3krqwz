(function($) {
    'use strict';

    // Initialize Stratos Pay checkout
    function initStratosPayCheckout() {
        // Handle form submission
        $('form.woocommerce-checkout').on('submit', function(e) {
            if ($('#payment_method_stratos_pay').is(':checked')) {
                e.preventDefault();
                
                const $form = $(this);
                const $errors = $('.woocommerce-error');
                
                $form.block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                });

                $errors.remove();

                // Submit form via AJAX
                $.ajax({
                    type: 'POST',
                    url: wc_checkout_params.checkout_url,
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function(result) {
                        if (result.result === 'success' && result.stratos_pay_params) {
                            // Initialize popup
                            try {
                                checkout.init(result.stratos_pay_params);
                            } catch (error) {
                                console.error('Stratos Pay initialization error:', error);
                                $form.prepend('<div class="woocommerce-error">Unable to initialize payment. Please try again.</div>');
                                $form.unblock();
                            }
                        } else {
                            if (result.messages) {
                                $form.prepend(result.messages);
                            } else {
                                $form.prepend('<div class="woocommerce-error">An error occurred. Please try again.</div>');
                            }
                            $form.unblock();
                        }
                    },
                    error: function(xhr, textStatus, error) {
                        console.error('Checkout error:', error);
                        $form.prepend('<div class="woocommerce-error">An error occurred. Please try again.</div>');
                        $form.unblock();
                    }
                });
            }
        });
    }

    // Initialize on document ready
    $(document).ready(function() {
        initStratosPayCheckout();
    });

})(jQuery);