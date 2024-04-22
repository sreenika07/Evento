<?php

/**
 * Support for Square Payments
 */

namespace GoDaddy\MWC\WordPress\HeadlessCheckout;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('\GoDaddy\MWC\WordPress\HeadlessCheckout\SquarePayments')) {

    class SquarePayments {
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

            if (isset($input['paymentMethod']) && $input['paymentMethod'] === 'square_credit_card') {
                // check if a subscription exists, if so tokenize
                $is_subscription = (class_exists('WC_Subscriptions_Cart') && \WC_Subscriptions_Cart::cart_contains_subscription() ? true : false);
                if ($is_subscription) {
                    $_POST['wc-square-credit-card-tokenize-payment-method'] = true;
                }
            }
        }

        /**
         * Hook into woo gql checkout before payment is processed to add payment info from meta data.
         */
        public function gqlCheckoutAction($order_data, $input, $context, $info) {

            if (isset($order_data['payment_method']) && $order_data['payment_method'] === 'square_credit_card') {
                $meta = [];
                foreach ($input['metaData'] as $key => $value) {
                    $meta[$value['key']] = $value['value'];
                }

                if (isset($meta['_wc-payment-token']) && !empty($meta['_wc-payment-token'])) {
                    $_POST['wc-square-credit-card-payment-token'] = $meta['_wc-payment-token'];
                }

                if (isset($meta['_wc-square-payment-nonce']) && !empty($meta['_wc-square-payment-nonce'])) {
                    $_POST['wc-square-credit-card-payment-nonce'] = $meta['_wc-square-payment-nonce'];
                }

                if (isset($meta['_wc-square-last-four']) && !empty($meta['_wc-square-last-four'])) {
                    $_POST['wc-square-credit-card-account-number'] = $meta['_wc-square-last-four'];
                }

                if (isset($meta['_wc-square-exp-month']) && !empty($meta['_wc-square-exp-month'])) {
                    $_POST['wc-square-credit-card-exp-month'] = $meta['_wc-square-exp-month'];
                }

                if (isset($meta['_wc-square-exp-year']) && !empty($meta['_wc-square-exp-year'])) {
                    $_POST['wc-square-credit-card-exp-year'] = $meta['_wc-square-exp-year'];
                }

                if (isset($meta['_wc-square-card-type']) && !empty($meta['_wc-square-card-type'])) {
                    $_POST['wc-square-credit-card-card-type'] = $meta['_wc-square-card-type'];
                }

                if (isset($meta['_wc-square-postal-code']) && !empty($meta['_wc-square-postal-code'])) {
                    $_POST['wc-square-credit-card-payment-postcode'] = $meta['_wc-square-postal-code'];
                }

                if (isset($meta['_wc-square-csc']) && !empty($meta['_wc-square-csc'])) {
                    $_POST['wc-square-credit-card-csc'] = $meta['_wc-square-csc'];
                }

                if (isset($meta['_wc-square-tokenize-payment-method']) && !empty($meta['_wc-square-tokenize-payment-method'])) {
                    $_POST['wc-square-credit-card-tokenize-payment-method'] = $meta['_wc-square-tokenize-payment-method'];
                }

                if (isset($meta['_wc-square-verification-token']) && !empty($meta['_wc-square-verification-token'])) {
                    $_POST['wc-square-credit-card-verification-token'] = $meta['_wc-square-verification-token'];
                }
            }
        }
    }
    new SquarePayments();
}
