<?php

namespace GoDaddy\MWC\WordPress\HeadlessCheckout;

class ShippingLabels {

    public function __construct() {
        $this->registerActions();
    }


    public function registerActions() {
        add_filter('woocommerce_checkout_fields', [$this, 'maybeRequirePhone']);
    }

    /**
     * Shipping Labels requires a shipping phone
     */
    public function maybeRequirePhone($fields) {
        if (class_exists('\GoDaddy\WordPress\MWC\Core\Features\Shipping\Shipping') && \GoDaddy\WordPress\MWC\Core\Features\Shipping\Shipping::shouldLoad()) {
            if (isset($fields['shipping']['shipping_phone'])) {
                $fields['shipping']['shipping_phone']['required'] = true;
            } else {
                $fields['shipping']['shipping_phone'] = [
                    'type' => 'text',
                    'label' => __('Phone number', 'woocommerce'),
                    'placeholder' => _x('Phone number', 'placeholder', 'woocommerce'),
                    'required' => true,
                ];
            }
        }
        return $fields;
    }
}

new ShippingLabels();
