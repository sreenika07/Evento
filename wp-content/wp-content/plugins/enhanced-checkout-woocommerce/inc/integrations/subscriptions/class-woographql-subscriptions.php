<?php

/**
 * Support for WooCommerce Subscriptions
 * Props to WPGraphQL\WooCommerce and Geoff Taylor
 */

namespace GoDaddy\MWC\WordPress\HeadlessCheckout;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('\WooGraphQL_Subscriptions')) {

    class WooGraphQL_Subscriptions {
        public function __construct() {
            $this->load();
        }

        /**
         * Add WooCommerce Subscription product types.
         *
         * @return array
         */
        public function add_product_types($product_types) {
            $product_types['subscription']                  = 'SubscriptionProduct';
            $product_types['variable-subscription'] = 'SubscriptionVariableProduct';

            return $product_types;
        }

        public function add_product_enums($values) {
            $values = array_merge(
                array(
                    'SUBSCRIPTION'         => array(
                        'value'       => 'subscription',
                        'description' => __('A subscription product', 'gd-checkout'),
                    ),
                    'VARIABLE_SUBSCRIPTION' => array(
                        'value'       => 'variable-subscription',
                        'description' => __('A subscription variable product', 'gd-checkout'),
                    ),
                    'SUBSCRIPTION_VARIATION' => array(
                        'value'       => 'subscription_variation',
                        'description' => __('A subscription variable product variation', 'gd-checkout'),
                    ),
                ),
                $values
            );

            return $values;
        }

        /**
         * Sets up WooGraphQL schema.
         */
        private function load() {
            // Add product types
            add_filter('graphql_woocommerce_product_types', array($this, 'add_product_types'), 10);

            // Add product enumeration values.
            add_filter('graphql_product_types_enum_values', array($this, 'add_product_enums'), 10);

            add_action('graphql_register_types', array($this, 'register_subscription_types'), 10, 1);


            require_once(plugin_dir_path(__FILE__) . '/class-subscription-product.php');
        }

        /**
         * Add WooCommerce Subscription product types.
         *
         * @return array
         */
        public function register_subscription_types() {
            register_graphql_enum_type(
                'DisplayContextEnum',
                array(
                    'description' => __('WC Subscriptions query display context', 'wp-graphql-woocommerce'),
                    'values'      => array(
                        'RAW'     => array('value' => 'raw'),
                        'HTML'    => array('value' => 'html'),
                        'DEFAULT' => array('value' => 'default')
                    ),
                )
            );

            register_graphql_enum_type(
                'PricingPropertiesEnum',
                array(
                    'description' => __('Properties that make up the subscription price', 'wp-graphql-woocommerce'),
                    'values'      => array(
                        'SUBSCRIPTION_PRICE'  => array('value' => 'subscription_price'),
                        'SUBSCRIPTION_PERIOD' => array('value' => 'subscription_period'),
                        'SUBSCRIPTION_LENGTH' => array('value' => 'subscription_length'),
                        'SIGN_UP_FEE'         => array('value' => 'sign_up_fee'),
                        'TRIAL_LENGTH'        => array('value' => 'trial_length'),
                    ),
                )
            );
        }
    }
}


new WooGraphQL_Subscriptions();
