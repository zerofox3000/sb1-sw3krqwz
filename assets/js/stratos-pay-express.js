import { registerExpressPaymentMethod } from '@woocommerce/blocks-registry';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { PaymentMethodLabel, PaymentMethod } from '@woocommerce/blocks-checkout';

const StratosPayButton = () => {
  const handleClick = (e) => {
    e.preventDefault();
    if (typeof justwallet !== 'undefined') {
      const widgetParams = {
        public_key: window.wc_stratos_pay_params.public_key,
        transaction_reference: 'order_' + Math.floor(Math.random() * 1000000000 + 1),
        amount: window.wc_stratos_pay_params.total * 100,
        currency: window.wc_stratos_pay_params.currency,
        email: window.wc_stratos_pay_params.billing_email,
        return_url: window.wc_stratos_pay_params.return_url,
        customization: {
          title: window.wc_stratos_pay_params.store_name,
          description: 'Express Checkout',
          logo: window.wc_stratos_pay_params.store_logo
        }
      };
      justwallet.init(widgetParams);
    }
  };

  return (
    <button className="stratos-pay-express-button" onClick={handleClick}>
      Pay with Stratos Pay
    </button>
  );
};

// Register express checkout button
registerExpressPaymentMethod({
  name: 'stratos_pay_express',
  content: <StratosPayButton />,
  edit: <StratosPayButton />,
  canMakePayment: () => true,
  paymentMethodId: 'stratos_pay',
  supports: {
    features: ['products']
  }
});

// Register payment method button
registerPaymentMethod({
  name: 'stratos_pay',
  label: <PaymentMethodLabel title="Pay with Stratos Pay" />,
  content: <PaymentMethod><StratosPayButton /></PaymentMethod>,
  edit: <PaymentMethod><StratosPayButton /></PaymentMethod>,
  canMakePayment: () => true,
  ariaLabel: 'Pay with Stratos Pay',
  supports: {
    features: ['products']
  }
});