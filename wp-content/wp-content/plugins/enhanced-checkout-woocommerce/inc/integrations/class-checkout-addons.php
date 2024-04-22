<?php

namespace GoDaddy\MWC\WordPress\HeadlessCheckout;

class CheckoutAddOns {

    public function __construct() {
        $this->registerActions();
    }


    public function registerActions() {
        add_action('woocommerce_before_calculate_totals', [$this, 'addCheckoutAddonsToPostData']);
    }

    /**
     * Fees are not properly calculated in woographql, this is a hacky fix
     * See https://godaddy-corp.atlassian.net/browse/MWC-11243
     */
    public function addCheckoutAddonsToPostData() {
        $addons = \WC()->session->checkout_add_ons;

        if (!isset($addons['fees'])) return;
        foreach ($addons['fees'] as $fee) {
            if (isset($fee['value']) && $fee['value'] > 0) {
                $data = '&' . $fee['id'] . '=' . $fee['value'];
                if (isset($_POST['post_data'])) {
                    $_POST['post_data'] .= $data;
                } else {
                    $_POST['post_data'] = $data;
                }
            }
        }


        return !empty($_POST['post_data']) ? $_POST['post_data'] : '';
    }
}

new CheckoutAddOns();
