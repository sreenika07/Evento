<?php
/*
 * Get API data 
 */

namespace GoDaddy\MWC\WordPress\HeadlessCheckout;

use GraphQL\Error\UserError;
use WPGraphQL\WooCommerce\Data\Mutation\Checkout_Mutation;
use GoDaddy\MWC\WordPress\HeadlessCheckout\Stripe;
use WooCommerce\Square\Gateway as SquareGateway;
use \SkyVerge\WooCommerce\Checkout_Add_Ons\Add_Ons\Add_On_Factory;
use \SkyVerge\WooCommerce\Checkout_Add_Ons\Frontend\Frontend;
use GoDaddy\MWC\WordPress\HeadlessCheckout\CheckoutAddOns;
use GoDaddy\MWC\WordPress\HeadlessCheckout\WooGQL_Methods;

class Resolvers {

    public function __construct() {
    }

    /**
     * Get saved payment methods from token API 
     * @param array $input [ 'userId' => $user_id, 'gatewayId' => $gateway_id ]
     * @return array PaymentToken
     */
    public static function getPaymentTokensForUser($input) {

        if (get_current_user_id() !== $input['userId'] && !current_user_can('manage_options')) {
            throw new UserError('You do not have permission to view this data.');
        }

        if (function_exists('\WC') && isset($input['userId']) && isset($input['gatewayId'])) {

            $payment_methods = [];

            if ($input['gatewayId'] === 'square_credit_card' || $input['gatewayId'] === 'poynt_credit_card') {

                // Square uses a different token handler
                $gateway = \WC()->payment_gateways()->payment_gateways()[$input['gatewayId']];
                if (!$gateway || !method_exists($gateway, 'get_payment_tokens_handler')) {
                    throw new UserError('Gateway does not support payment tokens.');
                }

                $tokens = $gateway->get_payment_tokens_handler()->get_tokens($input['userId']);

                foreach ($tokens as $token) {
                    if ($token->get_id()) {
                        $payment_methods[] = [
                            'id' => $token->get_id(),
                            'token' => $token->get_id(),
                            'last4' => $token->get_last_four(),
                            'type' => $token->get_card_type(),
                            'expiryMonth' => $token->get_exp_month(),
                            'expiryYear' => $token->get_exp_year(),
                        ];
                    }
                }
            } else {
                $tokens = \WC_Payment_Tokens::get_customer_tokens($input['userId'], $input['gatewayId']);

                foreach ($tokens as $token) {
                    if ($token->get_token()) {
                        $payment_methods[] = [
                            'id' => $token->get_id(),
                            'token' => $token->get_token(),
                            'last4' => $token->get_last4(),
                            'type' => $token->get_card_type(),
                            'expiryMonth' => $token->get_expiry_month(),
                            'expiryYear' => $token->get_expiry_year(),
                        ];
                    }
                }
            }

            return [
                'paymentTokens' => $payment_methods
            ];
        }

        return [];
    }

    /**
     * Get Square settings
     * @return array [ 'sandboxMode' => $sandbox_mode, 'applicationId' => $application_id, 'locationId' => $location_id]
     */

    public static function getSquareSettings() {
        if (class_exists('\WooCommerce\Square\Gateway')) {
            $gateway = new SquareGateway();

            return
                [
                    'sandboxMode' => $gateway->get_plugin()->get_settings_handler()->is_sandbox(),
                    'applicationId' => $gateway->get_application_id(),
                    'locationId' => $gateway->get_plugin()->get_settings_handler()->get_location_id()
                ];
        }

        return
            [
                'sandboxMode' => '',
                'applicationId' => '',
                'locationId' => ''
            ];
    }

    public static function stripePaymentIntent(array $input): array {
        $response = Stripe::upsertPaymentIntent([
            'currency' => isset($input['currency']) ? $input['currency'] : 'usd',
            'id' => isset($input['id']) ? $input['id'] : '',
            'setup_future_usage' => isset($input['setupFutureUsage']) ? $input['setupFutureUsage'] : '',
            'userEmail' => isset($input['userEmail']) ? $input['userEmail'] : '',
            'customerId' => isset($input['customerId']) ? $input['customerId'] : '',
        ]);

        return $response;
    }

    /**
     * Get saved payment methods for a user
     * @param array $input [ 'userId' => $user_id ]
     * @return array 
     */
    public static function stripePaymentMethods(array $input): array {
        if (!isset($input['userId'])) {
            throw new UserError('User ID is required');
        }

        if (get_current_user_id() !== $input['userId'] && !current_user_can('manage_options')) {
            throw new UserError('You do not have permission to view this data.');
        }

        $user = get_user_by('id', $input['userId']);
        $customer_id = Stripe::getStripeCustomerId($user->user_email);
        $response = Stripe::getSavedPaymentMethods($customer_id);
        return $response;
    }

    /**
     * Confirm a stripe payment intent 
     * @param array $input [ 'id' => $id, 'paymentMethodId' => $payment_method_id ]
     * @return array 
     */
    public static function confirmStripePaymentIntent(array $input): array {
        $response = Stripe::confirmPaymentIntent($input);
        return $response;
    }



    /**
     * Create an order with cart items and totals from the user session
     * Addresses and meta data are passed in the $input
     * @param array $input
     * @return array ['orderId' => $order_id]
     */
    public static function createOrderFromSession(array $input): array {
        $order_id = null;


        $create_order_data = self::convert_order_data($input);


        try {

            do_action('graphql_woocommerce_before_checkout', $create_order_data, $input, null, null);
            do_action('woocommerce_before_checkout_process');

            // create an order based on the session
            // copied from Checkout_Mutation::process_checkout() so it's similar to other checkouts
            WooGQL_Methods::update_session($create_order_data);
            // WooGQL_Methods::validate_checkout($create_order_data);
            WooGQL_Methods::process_customer($create_order_data);

            $order_id = WC()->checkout->create_order($create_order_data);

            $order = \wc_get_order($order_id);

            if (isset($input['orderNote'])) {
                $order->add_order_note($input['orderNote']);
            }

            if (isset($input['orderStatus'])) {
                $order->update_status($input['orderStatus']);
            }

            if (isset($input['transactionId'])) {
                $order->set_transaction_id($input['transactionId']);
            }

            $order->save();

            // save metaData to order
            Checkout_Mutation::update_order_meta($order_id, $input['metaData'], $input, null, null);
        } catch (UserError $e) {
            throw new UserError("Could not create order. " . $e->getMessage());
        }

        return [
            'orderId' => $order_id,
            'redirectUrl' => \wc_get_order($order_id)->get_checkout_order_received_url()
        ];
    }

    /**
     * Mark an order as paid after a successful payment on the front end
     * @param array $input [ 'orderId' => $order_id, 'transactionId' => $transaction_id ]
     * @return array ['orderId' => $order_id, 'orderStatus' => $order_status, 'redirectUrl' => $redirect_url]
     */
    public static function completeOrder(array $input): array {

        try {
            $order_id = $input['orderId'];
            $order = wc_get_order($order_id);

            if (!$order) {
                throw new UserError("Order not found");
            }
            if (isset($input['transactionId'])) {
                $order->set_transaction_id($input['transactionId']);
            }
            $order->payment_complete();
            wc_empty_cart();

            return [
                'orderId' => $order_id,
                'orderStatus' => $order->get_status(),
                'redirectUrl' => $order->get_checkout_order_received_url(),
            ];
        } catch (UserError $e) {
            throw new UserError($e->getMessage());
        }
    }

    /**
     * Convert order data from graphql mutation to format that WC_Checkout::create_order() expects
     * @param Array $input
     * @return Array $order_data
     */
    public static function convert_order_data($input) {
        try {
            $return_fields = Checkout_Mutation::prepare_checkout_args($input, null, null);
        } catch (UserError $e) {
            throw new UserError("Error preparing order data: " . $e->getMessage());
        }

        return $return_fields;
    }

    /**
     * Login a user and set an auth cookie
     * @param string $username
     * @param string $password
     * @return array ['result' => 'success', 'user_id' => $user_id]
     */
    public static function loginSetCookie(string $username, string $password): array {

        /**
         * Log the user in, which sets an auth cookie
         */
        $user = wp_signon([
            'user_login'    => sanitize_user($username),
            'user_password' => trim($password),
            'remember'      => true,
        ]);

        /**
         * If the authentication fails return a error
         */
        if (is_wp_error($user)) {
            $error_message = !empty($user->get_error_message()) ? $user->get_error_message() : 'invalid login';
            throw new UserError(strip_tags($error_message));
        }

        if (empty($user->data->ID)) {
            throw new UserError(__('The user could not be found', 'gd-checkout'));
        }

        /**
         * Return result to the client
         */
        $response = [
            'result'    => 'success',
            'user_id'   => $user->data->ID,
        ];

        return $response;
    }

    /**
     * Add a cart fee using Checkout Add Ons plugin
     * @param array $input [ 'id' => $id, 'value' => $value ] 
     */
    public static function addCheckoutAddonFee($input) {

        $checkout_addons_class = new CheckoutAddOns();
        // handle multiple fees
        $post_data = $checkout_addons_class->addCheckoutAddonsToPostData();

        // this is the format that the Checkout Add Ons plugin expects
        $_POST['post_data'] = $post_data . '&' . $input['id'] . '=' . $input['value'];

        \WC()->cart->calculate_fees();
        \WC()->cart->calculate_totals();

        return [
            'result' => 'success',
            'fees' => \WC()->cart->get_fees(),
        ];
    }

    /**
     * Convert post data into WooCommerce format
     * @param array $data
     * @param string $replace_key
     * @return array
     */
    public static function getNormalizedAddressFields($data, $replace_key) {

        $default_address_fields = ['firstName', 'lastName', 'address1', 'address2', 'city', 'state', 'postcode', 'country', 'phone', 'company'];

        $return_fields = [];

        foreach ($data as $key => $field) {

            if (isset($field['enabled']) && $field['enabled'] === false) {
                continue;
            }

            $normalized_key = str_replace($replace_key, '', $key);

            // change underscores in address to camelCase to match app fields
            $normalized_key = str_replace('_name', 'Name', $normalized_key);
            $normalized_key = str_replace('_1', '1', $normalized_key);
            $normalized_key = str_replace('_2', '2', $normalized_key);

            $return_fields[$normalized_key] = [
                'id' => $key,
                "validate" => isset($field['validate']) ? $field['validate'] : [],
                "options" => isset($field['options']) ? $field['options'] : [],
                "props" => [
                    'label' => isset($field['label']) ? $field['label'] : '',
                    'required' => isset($field['required']) ? $field['required'] : false,
                    'autoComplete' => isset($field['autocomplete']) ? $field['autocomplete'] : '',
                    'type' => isset($field['type']) ? $field['type'] : 'text',
                    'placeholder' => (isset($field['placeholder']) && $field['placeholder'] !== '' ? $field['placeholder'] : $field['label']),
                ]
            ];

            // default address fields should not have a name, because we are handling that in the app, but custom fields need a name
            if (!in_array($normalized_key, $default_address_fields)) {
                $return_fields[$normalized_key]['props']['name'] = $key;
            }
        }

        return $return_fields;
    }

    /**
     * Handle custom checkout fields
     * @param array $data
     * @param string $tab
     * @return array
     */
    public static function getAdditionalFields($data, $tab) {
        $return_fields = [];

        foreach ($data as $key => $field) {

            if (isset($field['enabled']) && $field['enabled'] === false) {
                continue;
            }

            $props = [];
            $props["name"] = "{$tab}-{$key}";
            $props["placeholder"] = (isset($field['placeholder']) && $field['placeholder'] !== '' ? $field['placeholder'] : $field['label']);
            $props["label"] = isset($field['label']) ? $field['label'] : '';
            $props["required"] = isset($field['required']) ? $field['required'] : false;
            $props["type"] = isset($field['type']) ? $field['type'] : 'text';
            $props["autoComplete"] = isset($field['autocomplete']) ? $field['autocomplete'] : '';

            $return_fields[] = [

                "id" => $key,
                "validate" => isset($field['validate']) ? $field['validate'] : [],
                "props" => $props,
                "options" => isset($field['options']) ? $field['options'] : [],
            ];
        }

        return $return_fields;
    }

    /**
     * Handle fees (checkout add ons plugin)
     * @param array $data
     * @param string $tab
     * @return array
     */
    public static function getFeeFields($tab = 'order') {
        $return_fields = [];

        if (!class_exists('\SkyVerge\WooCommerce\Checkout_Add_Ons\Add_Ons\Add_On_Factory')) {
            return $return_fields;
        }

        $addons = Add_On_Factory::get_add_ons();

        $Add_On_Factory_Class = new Frontend();

        foreach ($addons as $add_on) {

            $id    = esc_attr($add_on->get_id());
            $name  = $add_on->get_name();

            // continue to next add-on if an add-on of the same name was already added
            if (!$add_on->get_enabled()) {
                continue;
            }

            // continue to next add-on if the add-on should not be displayed as per display rules
            if (!$add_on->should_display()) {
                continue;
            }

            $value = $Add_On_Factory_Class->checkout_get_add_on_value($add_on->get_default_value(), $id);

            $options = [];
            $default = null;

            if ($add_on->has_options()) {

                foreach ($add_on->get_options('render') as $option) {

                    $cost_type = isset($option['adjustment_type']) ? $option['adjustment_type'] : 'fixed';
                    $cost_html = $add_on->get_cost_html($option['adjustment'], $cost_type);

                    // remove HTML tags from select option costs
                    if ('select' === $add_on->get_type()) {
                        $cost_html = html_entity_decode(wp_strip_all_tags($cost_html));
                    }

                    $key = trim($Add_On_Factory_Class->get_formatted_label($option['label'], $option['label'], $cost_html));

                    // key and value are reversed on purpose
                    $value   = sanitize_title(esc_html($option['label']), '', 'wc_checkout_add_ons_sanitize');

                    $options[$key] = $value;

                    if ($option['selected']) {
                        $default = $key;
                    }
                }
            }

            $props = [];
            $props["name"] = "{$name}";
            $props["required"] = $add_on->is_required();
            $props["type"] = $add_on->get_type();
            $props["label"] = $Add_On_Factory_Class->get_formatted_label($add_on->get_name(), $add_on->get_label());
            $props["description"] = $add_on->get_description();
            $props["defaultValue"] = $Add_On_Factory_Class->checkout_get_add_on_value($default, $id);

            $return_fields[] = [
                "id" => $id,
                "validate" => [],
                "type_special" => "fee",
                "props" => $props,
                "options" => $options
            ];
        }

        return $return_fields;
    }

    /**
     * Get allowed sell and ship to countries
     */
    public static function getAllowedCountries() {
        $countries = new \WC_Countries();

        $sellToCountries = [];

        foreach ($countries->get_allowed_countries() as $key => $value) {
            $sellToCountries[] = [
                'code' => $key,
                'name' => $value,
            ];
        }

        $shipToCountries = [];

        foreach ($countries->get_shipping_countries() as $key => $value) {
            $shipToCountries[] = [
                'code' => $key,
                'name' => $value,
            ];
        };

        return [
            'sellToCountries' => $sellToCountries,
            'shipToCountries' => $shipToCountries
        ];
    }

    /**
     * Get the checkout tab data
     * @return array
     */
    public static function getCheckoutTabs() {
        $woo_fields = \WC()->checkout->get_checkout_fields();

        $normalized_shipping_fields = Resolvers::getNormalizedAddressFields($woo_fields['shipping'], 'shipping_');
        $normalized_billing_fields = Resolvers::getNormalizedAddressFields($woo_fields['billing'], 'billing_');
        $order_fields = Resolvers::getAdditionalFields($woo_fields['order'], 'order');
        $fee_fields = Resolvers::getFeeFields('order');


        $showCoupons = get_option('woocommerce_enable_coupons') === 'yes' && get_option('wc_url_coupons_hide_coupon_field_checkout') !== 'yes';

        $discount_field = $showCoupons ? [[
            "component" => "DiscountCode",
            "id" => "discount-code",
            "props" => [
                "label" => "Enter discount code",
                "placeholder" => "Coupon code",
                "autoCapitalize" => "none",
                "autoComplete" => "off"
            ]

        ]] : [];

        $cart_has_subscription = (class_exists('WC_Subscriptions_Cart') && \WC_Subscriptions_Cart::cart_contains_subscription() ? true : false);

        // guest checkout and account creation both need to be checked to show this. Subscriptions automatically create accounts.
        $showCreateAccountOption = (!$cart_has_subscription && get_option('woocommerce_enable_guest_checkout') === 'yes' && get_option('woocommerce_enable_signup_and_login_from_checkout') === 'yes');


        $create_account = [];
        if ($showCreateAccountOption) {

            $create_account = [
                [
                    "component" => "Checkbox",
                    "id" => "create-account",
                    "props" => [
                        "label" => "Create an account?",
                        "name" => "create-account",
                        "type" => "checkbox",
                        "className" => "my-1",
                        "required" => false,
                        "description" => "An account will be created using your email upon checkout. If you are a returning customer please login at the beginning of this form."
                    ]
                ]
            ];
        } elseif (!$showCreateAccountOption && $cart_has_subscription) {
            $create_account = [
                [
                    "component" => "Text",
                    "id" => "create-account-text",
                    "props" => [
                        "children" => "An account will be created using your email upon checkout. If you are a returning customer please login at the beginning of this form.",
                        "className" => "text-sm text-gray-500"
                    ]
                ]
            ];
        }

        $terms_checkbox = wc_terms_and_conditions_page_id() && !empty(wc_get_terms_and_conditions_checkbox_text()) ? [
            [
                "component" => "RequiredCheckbox",
                "id" => "terms-checkbox",
                "props" => [
                    "title" => wc_replace_policy_page_link_placeholders(wc_get_terms_and_conditions_checkbox_text()),
                    "name" => "terms-checkbox",
                    "type" => "checkbox",
                    "className" => "my-1 text-sm text-gray-600",
                    "required" => true,
                ]
            ]
        ] : [];

        $login_field = get_option('woocommerce_enable_checkout_login_reminder') === 'yes' ? [
            [
                "component" => "InlineLogin",
                "id" => "inline-login",
            ],
        ] : [];

        $tabs = [
            [
                "id" => "contact",
                "label" => "Contact",
                "fields" => [
                    [
                        "id" => "express-buttons",
                        "component" => "ExpressButtons",
                        "props" => [],
                    ],
                    [
                        "component" => "Text",
                        "id" => "contact-heading",
                        "props" => [
                            "as" => "h2",
                            "children" => "Contact Information"
                        ]
                    ],
                    [
                        "component" => "TextField",
                        "id" => "contact-email",
                        "validate" => $normalized_billing_fields['email']['validate'],
                        "props" => [
                            "name" => "email",
                            "label" => $normalized_billing_fields['email']['props']['label'],
                            "type" => "email",
                            "placeholder" => isset($normalized_billing_fields['email']['props']['placeholder']) ? $normalized_billing_fields['email']['props']['placeholder'] : $normalized_billing_fields['email']['props']['label'],
                            "tabIndex" => 0,
                            "inputMode" => "email",
                            "autoComplete" => "on",
                            "autoCapitalize" => "none",
                            "errorMessage" => "Please enter a valid email.",
                            "required" => isset($normalized_billing_fields['email']['props']) && isset($normalized_billing_fields['email']['props']['required']) ? $normalized_billing_fields['email']['props']['required'] : false
                        ]
                    ],
                    ...$login_field,
                    [
                        "component" => "ShippingAddress",
                        "id" => "shipping-address-autocomplete",
                        "props" => [
                            "title" => "Shipping Address",
                            "addressData" => $normalized_shipping_fields
                        ]
                    ]

                ]
            ],
            [
                "id" => "shipping",
                "label" => "Shipping",
                "fields" => [
                    [
                        "component" => "Text",
                        "id" => "shipping-method-heading",
                        "props" => [
                            "children" => "Shipping Method",
                            "as" => "h2"
                        ]
                    ],
                    [
                        "component" => "ShippingMethods",
                        "id" => "shipping-methods"
                    ]
                ]
            ],
            [
                "id" => "payment",
                "label" => "Payment",
                "fields" => [
                    ...$discount_field,
                    [
                        "component" => "Text",
                        "id" => "billing-information-heading",
                        "props" => [
                            "as" => "h2",
                            "children" => "Billing Address"
                        ]
                    ],
                    [
                        "component" => "BillingAddress",
                        "id" => "billing-address-autocomplete",
                        "props" => [
                            "label" => "Billing Address",
                            "addressData" => $normalized_billing_fields
                        ]
                    ],
                    ...$create_account,
                    [
                        "component" => "Text",
                        "id" => "payment-information-heading",
                        "props" => [
                            "as" => "h2",
                            "children" => "Payment Information"
                        ]
                    ],
                    [
                        "component" => "PaymentMethod",
                        "id" => "payment-method"
                    ],

                    ...$order_fields,
                    ...$fee_fields,
                    ...$terms_checkbox
                ]
            ]
        ];

        return $tabs;
    }

    /**
     * Get privacy policy and terms links if they are set
     */
    public static function getFooterLinks() {

        $footerLinks = [];
        if (wc_privacy_policy_page_id()) {

            $footerLinks[] = [
                "title" => "Privacy Policy",
                "content" => wc_replace_policy_page_link_placeholders(get_option('woocommerce_checkout_privacy_policy_text'))
            ];
        }

        if (wc_terms_and_conditions_page_id()) {
            $footerLinks[] = [
                "title" => "Terms and conditions",
                "content" => wc_replace_policy_page_link_placeholders(get_option('woocommerce_checkout_terms_and_conditions_checkbox_text'))
            ];
        }

        return $footerLinks;
    }
    /**
     * Get the checkout data
     *
     * @return array
     */
    public static function getCheckoutData() {

        return [
            'checkoutData' => json_encode(
                [
                    "colors" => [
                        "primary" => get_option('gd_checkout_primary_color', '#000000'),
                        "contrast" => get_option('gd_checkout_contrast_color', '#ffffff'),
                    ],
                    "title" => stripslashes(get_option('gd_checkout_title', get_option('blogname'))),
                    "logo" => get_option('gd_checkout_logo'),
                    "tabs" => Resolvers::getCheckoutTabs(),
                    "footerLinks" => Resolvers::getFooterLinks(),
                    "hooks" => [
                        "belowContent" => "",
                        "aboveContent" => ""
                    ],
                    "settings" => [
                        "guestCheckout" => get_option('woocommerce_enable_guest_checkout'),
                        "showLogin" => get_option('woocommerce_enable_checkout_login_reminder'),
                        "enableTaxes" => get_option('woocommerce_calc_taxes'),
                        "enableShipping" => get_option('woocommerce_enable_shipping_calc'),
                    ]
                ]
            )
        ];
    }
}
