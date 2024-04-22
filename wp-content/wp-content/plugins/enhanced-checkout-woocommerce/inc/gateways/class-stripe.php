<?php

namespace GoDaddy\MWC\WordPress\HeadlessCheckout;

use GraphQL\Error\UserError;
use GoDaddy\WordPress\MWC\Core\Payments\Stripe as MWC_Stripe;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('\GoDaddy\MWC\WordPress\HeadlessCheckout\Stripe')) {

    /**
     * Stripe Intent Controller. 
     */
    class Stripe {

        public function __construct() {
            $this->load();
        }

        private function load() {
            add_action('graphql_woocommerce_before_checkout', [$this, 'gqlCheckoutAction'], 10, 4);
            add_action('graphql_woocommerce_before_checkout_meta_save', [$this, 'gqlCheckoutMetaSaveAction'], 10, 5);
        }
        /**
         * Happens after the order is created, but before payment is processed
         */
        public function gqlCheckoutMetaSaveAction($order, $meta_data, $input, $context, $info) {

            $flattened_meta = [];
            foreach ($meta_data as $key => $value) {
                $flattened_meta[$value['key']] = $value['value'];
            }

            $is_subscription = (class_exists('WC_Subscriptions_Cart') && \WC_Subscriptions_Cart::cart_contains_subscription() ? true : false);

            if (isset($input['paymentMethod']) && $input['paymentMethod'] === 'stripe') {

                // add customer id to order, required for subscriptions
                if ($is_subscription) {
                    $stripe = self::_getStripeClient();

                    // fix bug where billing email doesn't match stripe customer, causing failed renewals
                    $pm_result = $stripe->paymentMethods->retrieve(
                        $flattened_meta['_stripe_source_id'],
                        []
                    );

                    if ($pm_result && $pm_result->customer) {
                        $order->update_meta_data('_stripe_customer_id', $pm_result->customer);
                    } else {
                        $stripe_customer_id = $this->getOrCreateCustomer($order->get_billing_email());
                        $order->update_meta_data('_stripe_customer_id', $stripe_customer_id);
                    }

                    Logger::log('Stripe order meta ' . print_r($order->get_meta_data(), true) . PHP_EOL);

                    $order->save();
                }
            }
        }

        /**
         * Happens before order is created 
         */
        public function gqlCheckoutAction($order_data, $input, $context, $info) {
            if (isset($order_data['payment_method']) && $order_data['payment_method'] === 'stripe') {

                $meta = [];
                foreach ($input['metaData'] as $key => $value) {
                    $meta[$value['key']] = $value['value'];
                }

                $_POST['payment_method'] = 'stripe';

                // paying with a saved card
                if (isset($meta['_stripe_source_id']) && !empty($meta['_stripe_source_id']) && isset($meta['_stripe-use-saved-payment-method']) && !empty($meta['_stripe-use-saved-payment-method'])) {

                    $_POST['wc-stripe-payment-token'] = $meta['_stripe_source_id'];
                }

                Logger::log('Stripe $_POST data ' . print_r($_POST, true) . PHP_EOL);
            }
        }

        /**
         * Which Stripe plugin is loaded, WooCommerce or MWC?
         * 
         * @return string
         * @throws UserError
         */
        public static function getStripeIntegration(): string {
            if (class_exists('WC_Stripe')) {
                return 'WC_Stripe';
            } else if (class_exists('\GoDaddy\WordPress\MWC\Core\Payments\Stripe') && MWC_Stripe::isConnected()) {
                return 'MWC_Stripe';
            } else {
                throw new UserError('Stripe is not connected');
            }
        }

        /**
         * Get stripe settings based on integration
         * @return array
         */
        public static function getStripeSettings() {

            $stripeIntegration = self::getStripeIntegration();
            $settings = [
                'testMode' => 'no',
                'testPublishableKey' => '',
                'publishableKey' => '',
                'paymentRequestButtons' => '',
                'capture' => '',
                'enableTokenization' => ''
            ];

            $stripeSettings = get_option('woocommerce_stripe_settings');

            if ($stripeIntegration == 'WC_Stripe') {

                $settings = [
                    'testMode' => !empty($stripeSettings['testmode']) ? $stripeSettings['testmode'] : 'no',
                    'testPublishableKey' =>  !empty($stripeSettings['test_publishable_key']) ? $stripeSettings['test_publishable_key'] : '',
                    'publishableKey' =>  !empty($stripeSettings['publishable_key']) ? $stripeSettings['publishable_key'] : '',
                    'paymentRequestButtons' => !empty($stripeSettings['payment_request']) ? $stripeSettings['payment_request'] : '',
                    'capture' => !empty($stripeSettings['capture']) ? $stripeSettings['capture'] : '',
                    'enableTokenization' => !empty($stripeSettings['saved_cards']) ? $stripeSettings['saved_cards'] : 'no'
                ];
            } else if ($stripeIntegration == 'MWC_Stripe') {
                $settings = [
                    'testMode' => 'no',
                    'testPublishableKey' =>  '',
                    'publishableKey' =>  MWC_Stripe::getApiPublicKey(),
                    'paymentRequestButtons' => 'yes',
                    'capture' => !empty($stripeSettings['transaction_type']) && $stripeSettings['transaction_type'] === 'authorization' ? 'no' : '',
                    'enableTokenization' => !empty($stripeSettings['enable_tokenization']) ? $stripeSettings['enable_tokenization'] : 'no',
                ];
            }

            return $settings;
        }

        /**
         * Get the stripe keys
         * @return string secret key 
         * @throws UserError
         */
        private static function getSecretKey(): string {

            $stripeIntegration = self::getStripeIntegration();

            $secret_key = '';

            if ($stripeIntegration == 'WC_Stripe') {
                $stripeSettings    = get_option('woocommerce_stripe_settings');
                $testmode    = (isset($stripeSettings['testmode']) && 'yes' === $stripeSettings['testmode']) ? true : false;
                $test_secret_key = isset($stripeSettings['test_secret_key']) ? $stripeSettings['test_secret_key'] : '';
                $live_secret_key = isset($stripeSettings['secret_key']) ? $stripeSettings['secret_key'] : '';

                if (empty($stripeSettings)) {
                    throw new UserError('No WooCommerce Stripe keys set.');
                }

                if ($testmode && !empty($test_secret_key)) {
                    $secret_key = $test_secret_key;
                } else if (!empty($live_secret_key)) {
                    $secret_key = $live_secret_key;
                }
            } else if ($stripeIntegration == 'MWC_Stripe') {
                $secret_key = MWC_Stripe::getApiSecretKey();
            }

            return $secret_key;
        }

        /**
         * Get the Stripe client
         * @return \Stripe\StripeClient
         */
        private static function _getStripeClient(): \Stripe\StripeClient {

            if (!class_exists('\Stripe\Stripe')) {
                throw new UserError('Stripe is not connected');
            }

            $secret_key = self::getSecretKey();

            if (!$secret_key) {
                throw new UserError('Stripe secret key could not be determined. Check WooCommerce Stripe settings.');
            }

            $stripe = new \Stripe\StripeClient($secret_key);
            return $stripe;
        }

        /**
         * Check if a Stripe customer ID exists, if not create a new customer
         * @param string $user_email 
         * @return string $stripe_customer_id
         */
        public static function getOrCreateCustomer($user_email = ''): string {

            if (empty($user_email)) {
                $user = wp_get_current_user();
                $user_email = $user->user_email;
            }

            if (empty($user_email)) {
                throw new UserError('User email is required to create a Stripe Customer. ' . $user_email);
            }

            $stripe_customer_id = self::getStripeCustomerId($user_email);
            if (!empty($stripe_customer_id)) {
                return $stripe_customer_id;
            }

            try {

                $stripe = self::_getStripeClient();

                // try to create a customer in Stripe
                $customer = $stripe->customers->create([
                    'email' => $user_email,
                ]);

                return $customer->id;
            } catch (UserError $e) {
                throw new UserError($e->getMessage());
            }
        }

        /**
         * Returns the Stripe customer ID stored in WooCommerce if it exists
         * @param string $user_id 
         * @return string Stripe customer ID
         */
        public static function getStripeCustomerId(string $user_email) {

            $stripe = self::_getStripeClient();

            $result = $stripe->customers->search([
                'query' => 'email: "' . $user_email . '"',
            ]);

            return (!empty($result->data) ?  $result->data[0]->id : '');
        }


        /**
         * Get or create a Stripe payment intent
         * @param Array $args = ['currency' => $currency, 'id' => $id, 'setup_future_usage' => $setup_future_usage, 'userEmail' => $userEmail, 'customerId' => $customerId]
         * @return Array $intent
         * @throws UserError
         */
        public static function upsertPaymentIntent($args): array {

            $amount = WC()->cart->get_totals()['total'];

            if (!$amount) {
                throw new UserError('Could not get cart total from session.');
            }

            $amount = floatval($amount) * 100;
            $currency = $args['currency'] ?? 'USD';
            $stripeSettings = get_option('woocommerce_stripe_settings');

            $intent_args = [
                'amount' => $amount,
                'currency' => $currency,
            ];

            if ($stripeSettings && isset($stripeSettings['capture']) && $stripeSettings['capture'] == 'no') {
                $intent_args['capture_method'] = 'manual';
            }

            // if we have a subscription in the cart, we need to force creating a stripe customer, and setup_future_usage
            // doing that here so it applies to payment element and payment request buttons
            // make sure to send email in request if user logged out
            $is_subscription = (class_exists('WC_Subscriptions_Cart') && \WC_Subscriptions_Cart::cart_contains_subscription() ? true : false);

            if (!empty($args['setup_future_usage']) || $is_subscription) {
                $intent_args['setup_future_usage'] = 'off_session';
            }

            if (is_user_logged_in() || ($is_subscription && !empty($args['userEmail'])) || isset($intent_args['setup_future_usage'])) {
                $email = is_user_logged_in() ? wp_get_current_user()->user_email : $args['userEmail'];
                $stripe_customer_id = self::getOrCreateCustomer($email);
                if ($stripe_customer_id) {
                    $intent_args['customer'] = $stripe_customer_id;
                }
            }

            try {
                $stripe = self::_getStripeClient();

                if (!empty($args['id'])) {
                    $intent = $stripe->paymentIntents->update($args['id'], $intent_args);
                } else {
                    $intent = $stripe->paymentIntents->create($intent_args);
                }
            } catch (UserError $e) {
                throw new UserError('Could not get Stripe payment intent: ' . $e->getMessage());
            }


            $response = [
                'id' => $intent->id,
                'amount' => $intent->amount,
                'status' => $intent->status,
                'clientSecret' => $intent->client_secret,
                'paymentMethodTypes' => $intent->payment_method_types,
                'customer' => $intent->customer
            ];

            return $response;
        }

        /**
         * Get saved payment methods for a customer
         * @param string $customer_id
         * @return array paymentMethods is a JSON string
         */
        public static function getSavedPaymentMethods(string $stripe_customer_id): array {

            if (!$stripe_customer_id) {
                // don't need to display an error, just fail silently
                throw new UserError('Missing customer id or secret key.');
            }

            try {

                $stripe = self::_getStripeClient();

                $result = $stripe->customers->allPaymentMethods(
                    $stripe_customer_id,
                    ['type' => 'card']
                );

                return [
                    'paymentMethods' => json_encode($result->toArray()['data']),
                ];
            } catch (\Exception $e) {
                // don't need to display an error, just fail silently
                throw new UserError($e->getMessage());
            }
        }

        /**
         * Confirm a payment intent
         * Used by the payment request button (Apple Pay, Google Pay)
         * @param array $input = ['id' => $id, 'paymentMethodId' => $paymentMethodId]
         * @return array $response = ['id' => $id, 'status' => $status, 'amount' => $amount, 'clientSecret' => $clientSecret]
         */
        public static function confirmPaymentIntent(array $input): array {
            $payment_args = [
                'payment_method' => $input['paymentMethodId'],
            ];

            if (!empty($input['setupFutureUsage']) && is_user_logged_in()) {
                $payment_args['setup_future_usage'] = $input['setupFutureUsage'];
                $stripe_customer_id = self::getOrCreateCustomer();
                self::attachCustomerToPaymentIntent($input['id'], $stripe_customer_id);
            }

            try {
                $stripe = self::_getStripeClient();

                $payment_intent = $stripe->paymentIntents->confirm(
                    $input['id'],
                    $payment_args
                );

                return [
                    'id' => $payment_intent->id,
                    'amount' => $payment_intent->amount,
                    'status' => $payment_intent->status,
                    'clientSecret' => $payment_intent->client_secret,
                    'customer' => $payment_intent->customer,
                ];
            } catch (UserError $e) {
                throw new UserError('Could not confirm Stripe payment intent: ' . $e->getMessage());
            }
        }

        /**
         * Attach a customer to a payment intent
         * @param string $payment_intent_id
         * @param string $stripe_customer_id
         * @return array $payment_intent
         */
        public static function attachCustomerToPaymentIntent(string $payment_intent_id, string $stripe_customer_id): \Stripe\PaymentIntent {

            try {

                $stripe = self::_getStripeClient();

                $payment_intent = $stripe->paymentIntents->update(
                    $payment_intent_id,
                    ['customer' => $stripe_customer_id]
                );
                return $payment_intent;
            } catch (UserError $e) {
                throw new UserError('Could not attach customer to Stripe payment intent: ' . $e->getMessage());
            }
        }
    }

    new Stripe();
}
