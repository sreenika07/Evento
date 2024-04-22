<?php

namespace GoDaddy\MWC\WordPress\HeadlessCheckout;

/**
 * Anything copied from WooGraphQL, like protected methods, goes here so it's easy to update.
 * The reason these are copied is because WooGraphQL does not have a way to create an authorization only checkout, specifically with Stripe. If this is changed in the future, we can remove this class.
 * 
 */


class WooGQL_Methods {
    /**
     * Update customer and session data from the posted checkout data.
     *
     * @param array $data Order data.
     */
    public static function update_session($data) {
        // Update both shipping and billing to the passed billing address first if set.
        $address_fields = [
            'first_name',
            'last_name',
            'company',
            'email',
            'phone',
            'address_1',
            'address_2',
            'city',
            'postcode',
            'state',
            'country',
        ];

        foreach ($address_fields as $field) {
            self::set_customer_address_fields($field, $data);
        }
        WC()->customer->save();

        // Update customer shipping and payment method to posted method.
        $chosen_shipping_methods = WC()->session->get('chosen_shipping_methods');

        if (is_array($data['shipping_method'])) {
            foreach ($data['shipping_method'] as $i => $value) {
                $chosen_shipping_methods[$i] = $value;
            }
        }

        WC()->session->set('chosen_shipping_methods', $chosen_shipping_methods);
        WC()->session->set('chosen_payment_method', $data['payment_method']);

        // Update cart totals now we have customer address.
        WC()->cart->calculate_totals();
    }

    /**
     * Set address field for customer.
     *
     * @param string $field String to update.
     * @param array  $data  Array of data to get the value from.
     */
    public static function set_customer_address_fields($field, $data) {
        $billing_value  = null;
        $shipping_value = null;

        if (isset($data["billing_{$field}"]) && is_callable([WC()->customer, "set_billing_{$field}"])) {
            $billing_value  = $data["billing_{$field}"];
            $shipping_value = $data["billing_{$field}"];
        }

        if (isset($data["shipping_{$field}"]) && is_callable([WC()->customer, "set_shipping_{$field}"])) {
            $shipping_value = $data["shipping_{$field}"];
        }

        if (!is_null($billing_value) && is_callable([WC()->customer, "set_billing_{$field}"])) {
            WC()->customer->{"set_billing_{$field}"}($billing_value);
        }

        if (!is_null($shipping_value) && is_callable([WC()->customer, "set_shipping_{$field}"])) {
            WC()->customer->{"set_shipping_{$field}"}($shipping_value);
        }
    }

    /**
     * Create a new customer account if needed.
     *
     * @param array $data Checkout data.
     *
     * @throws UserError When not able to create customer.
     */
    public static function process_customer($data) {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
        $customer_id = apply_filters('woocommerce_checkout_customer_id', get_current_user_id());

        if (!is_user_logged_in() && (self::is_registration_required() || !empty($data['createaccount']))) {
            $username    = !empty($data['account_username']) ? $data['account_username'] : '';
            $password    = !empty($data['account_password']) ? $data['account_password'] : '';
            $customer_id = wc_create_new_customer(
                $data['billing_email'],
                $username,
                $password,
                [
                    'first_name' => !empty($data['billing_first_name']) ? $data['billing_first_name'] : '',
                    'last_name'  => !empty($data['billing_last_name']) ? $data['billing_last_name'] : '',
                ]
            );

            if (is_wp_error($customer_id)) {
                throw new UserError($customer_id->get_error_message());
            }

            wc_set_customer_auth_cookie($customer_id);

            // As we are now logged in, checkout will need to refresh to show logged in data.
            WC()->session->set('reload_checkout', true);

            // Also, recalculate cart totals to reveal any role-based discounts that were unavailable before registering.
            WC()->cart->calculate_totals();
        } //end if

        // On multisite, ensure user exists on current site, if not add them before allowing login.
        if ($customer_id && is_multisite() && is_user_logged_in() && !is_user_member_of_blog()) {
            add_user_to_blog(get_current_blog_id(), $customer_id, 'customer');
        }

        // Add customer info from other fields.
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
        if ($customer_id && apply_filters('woocommerce_checkout_update_customer_data', true, WC()->checkout())) {
            $customer = new \WC_Customer($customer_id);

            if (!empty($data['billing_first_name']) && '' === $customer->get_first_name()) {
                $customer->set_first_name($data['billing_first_name']);
            }

            if (!empty($data['billing_last_name']) && '' === $customer->get_last_name()) {
                $customer->set_last_name($data['billing_last_name']);
            }

            // If the display name is an email, update to the user's full name.
            if (is_email($customer->get_display_name())) {
                $customer->set_display_name($customer->get_first_name() . ' ' . $customer->get_last_name());
            }

            foreach ($data as $key => $value) {
                // Use setters where available.
                if (is_callable([$customer, "set_{$key}"])) {
                    $customer->{"set_{$key}"}($value);

                    // Store custom fields prefixed with wither shipping_ or billing_.
                } elseif (0 === stripos($key, 'billing_') || 0 === stripos($key, 'shipping_')) {
                    $customer->update_meta_data($key, $value);
                }
            }

            // Action hook to adjust customer before save.
            // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
            do_action('woocommerce_checkout_update_customer', $customer, $data);

            $customer->save();
        } //end if

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
        do_action('woocommerce_checkout_update_user_meta', $customer_id, $data);
    }

    /**
     * Is registration required to checkout?
     *
     * @since  3.0.0
     * @return boolean
     */
    public static function is_registration_required() {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
        return apply_filters('woocommerce_checkout_registration_required', 'yes' !== get_option('woocommerce_enable_guest_checkout'));
    }
}
