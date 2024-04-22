<?php

/**
 * Support for GoDaddy Payments
 */

namespace GoDaddy\MWC\WordPress\HeadlessCheckout;


if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('\GoDaddy\MWC\WordPress\HeadlessCheckout\GoDaddyPayments')) {

    class GoDaddyPayments {
        public function __construct() {
            $this->load();
        }
        private function load() {
            add_action('graphql_woocommerce_before_checkout', [$this, 'gqlBeforeCheckout'], 10, 4);
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

            if (isset($flattened_meta['_createdVia']) && !empty($flattened_meta['_createdVia'])) {
                $order->set_created_via($flattened_meta['_createdVia']);
                $order->save();
            }


            if (isset($input['paymentMethod']) && $input['paymentMethod'] === 'poynt') {
                // check if a subscription exists, if so save payment method for future renewals
                $is_subscription = (class_exists('WC_Subscriptions_Cart') && \WC_Subscriptions_Cart::cart_contains_subscription() ? true : false);

                $is_wallet_payment = strpos($order->created_via, 'google_pay') > -1 || strpos($order->created_via, 'apple_pay') > -1;

                // GDP wallets do not support tokenization
                if ($is_subscription && !$is_wallet_payment) {
                    $_POST['mwc-payments-poynt-tokenize-payment-method'] = true;
                }
            }
        }

        /**
         * Hook into woo gql checkout before order is created, and before payment
         */
        public function gqlBeforeCheckout($order_data, $input, $context, $info) {
            if (isset($order_data['payment_method']) && $order_data['payment_method'] === 'poynt') {

                $meta = [];
                foreach ($input['metaData'] as $key => $value) {
                    $meta[$value['key']] = $value['value'];
                }

                if (isset($meta['_mwc_payments_poynt_payment_nonce']) && !empty($meta['_mwc_payments_poynt_payment_nonce'])) {
                    $_POST['mwc-payments-poynt-payment-nonce'] = $meta['_mwc_payments_poynt_payment_nonce'];
                }

                if (isset($meta['_mwc_payments_poynt_payment_method_id']) && !empty($meta['_mwc_payments_poynt_payment_method_id'])) {
                    $_POST['mwc-payments-poynt-payment-method-id'] = $meta['_mwc_payments_poynt_payment_method_id'];
                }

                if (isset($meta['_mwc_payments_poynt_tokenize_payment_method']) && !empty($meta['_mwc_payments_poynt_tokenize_payment_method'])) {
                    $_POST['mwc-payments-poynt-tokenize-payment-method'] = $meta['_mwc_payments_poynt_tokenize_payment_method'];
                }

                if (isset($meta['_wc-payment-token']) && !empty($meta['_wc-payment-token'])) {
                    $_POST['mwc-payments-poynt-payment-method-id'] = $meta['_wc-payment-token'];
                }
            }
        }
    }

    new GoDaddyPayments();
}
