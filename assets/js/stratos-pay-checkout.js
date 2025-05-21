(function($) {
    'use strict';

    // Initialize Stratos Pay widget
    function initStratosPayWidget() {
        if (typeof justwallet === 'undefined') {
            console.error('Stratos Pay widget not loaded');
            return;
        }

        const order = wc_stratos_pay_params;
        
        const widgetParams = {
            public_key: order.public_key,
            transaction_reference: 'order_' + Math.floor(Math.random() * 1000000000 + 1),
            amount: order.total * 100, // Convert to cents
            currency: order.currency,
            email: order.billing_email,
            first_name: order.billing_first_name,
            last_name: order.billing_last_name,
            country: order.billing_country,
            state: order.billing_state,
            city: order.billing_city,
            zip_code: order.billing_postcode,
            address: order.billing_address_1,
            return_url: order.return_url,
            customization: {
                title: order.store_name,
                description: 'Order #' + order.order_id,
                logo: order.store_logo
            }
        };

        justwallet.init(widgetParams);
    }

    // Initialize on document ready
    $(document).ready(function() {
        if ($('#stratos-pay-payment-widget').length) {
            initStratosPayWidget();
        }

        // Add payment option button handler
        $('.stratos-pay-payment-button').on('click', function(e) {
            e.preventDefault();
            initStratosPayWidget();
        });
    });

})(jQuery);