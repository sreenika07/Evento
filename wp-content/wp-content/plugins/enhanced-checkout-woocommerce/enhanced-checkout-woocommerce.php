<?php

/*
 * Plugin Name: GoDaddy Enhanced Checkout for WooCommerce
 * Author: GoDaddy 
 * Description: Blazingly fast, mobile-optimized checkout for WooCommerce. Works with GoDaddy Payments and <a href="https://www.godaddy.com/help/use-the-enhanced-checkout-for-woocommerce-stores-41636" target="_blank">compatible plugins</a> for Stripe, PayPal, and Square.
 * Version: 1.0.8
*/

declare(strict_types=1);

namespace GoDaddy\MWC\WordPress;

class HeadlessCheckout {

    public function __construct() {
        $this->start();
    }

    public function start() {

        define('GD_CHECKOUT_DIR', plugin_dir_path(__FILE__));
        define('GD_CHECKOUT_URL', plugin_dir_url(__FILE__));
        define('GD_CHECKOUT_BASENAME', plugin_basename(__FILE__));
        define('NO_QL_SESSION_HANDLER', true); // fixes a woographql bug where cart is empty at checkout
        define('GD_CHECKOUT_PLUGIN_VERSION', '1.0.8');
        // Used for AWS script
        define('GD_CHECKOUT_SCRIPT_VERSION', '0.0.5');

        add_action('plugins_loaded', [$this, 'shouldLoad']);

        define('GD_CHECKOUT_SUPPORTED_GATEWAYS', ['bacs', 'cheque', 'cod', 'stripe', 'ppcp-gateway', 'square_credit_card', 'poynt_credit_card', 'poynt']);
    }


    public function loadFiles() {


        require_once(GD_CHECKOUT_DIR . 'vendor/autoload.php');

        if (!in_array('wp-graphql/wp-graphql.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            require_once(GD_CHECKOUT_DIR . 'vendor/wp-graphql/wp-graphql/wp-graphql.php');
        }

        if (!in_array('wp-graphql-woocommerce/wp-graphql-woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            require_once(GD_CHECKOUT_DIR . 'vendor/wp-graphql/wp-graphql-woocommerce/wp-graphql-woocommerce.php');
        }

        require_once(plugin_dir_path(__FILE__) . 'inc/class-logger.php');

        require_once(plugin_dir_path(__FILE__) . 'inc/api/graphql.php');

        require_once(plugin_dir_path(__FILE__) . 'inc/frontend.php');
        require_once(plugin_dir_path(__FILE__) . 'inc/api/resolvers.php');

        require_once(plugin_dir_path(__FILE__) . 'inc/gateways/class-stripe.php');

        require_once(plugin_dir_path(__FILE__) . 'inc/integrations/subscriptions/class-woographql-subscriptions.php');

        require_once(plugin_dir_path(__FILE__) . 'inc/integrations/class-woogql-methods.php');

        require_once(plugin_dir_path(__FILE__) . 'inc/gateways/class-square-payments.php');

        require_once(plugin_dir_path(__FILE__) . 'inc/gateways/class-godaddy-payments.php');

        require_once(plugin_dir_path(__FILE__) . 'inc/gateways/class-woo-poynt-plugin.php');

        require_once(plugin_dir_path(__FILE__) . 'inc/integrations/class-shipping-labels.php');


        if (class_exists('\WC_Checkout_Add_Ons_Loader')) {
            require_once(plugin_dir_path(__FILE__) . 'inc/integrations/class-checkout-addons.php');
        }
    }

    public function loadScripts() {
        if (!self::isCheckoutPage()) return;

        wp_enqueue_script('gd-checkout', plugin_dir_url(__FILE__) . 'assets/js/gd-checkout.js', [], GD_CHECKOUT_PLUGIN_VERSION, true);
        wp_enqueue_style('gd-checkout', plugin_dir_url(__FILE__) . 'assets/css/gd-checkout.css', [], GD_CHECKOUT_PLUGIN_VERSION);

        wp_localize_script(
            'gd-checkout',
            'gdCheckoutVars',
            array(
                'isCheckout' => is_checkout(),
                'wooCheckoutUrl' => wc_get_checkout_url(),
                'wooShopUrl' => get_permalink(wc_get_page_id('shop')),
            )
        );
    }

    public static function isCheckoutPage() {
        $shouldLoad = false;
        if (is_checkout()) {
            $shouldLoad = true;
        }

        return apply_filters('gd_checkout_should_load', $shouldLoad);
    }

    public function shouldLoad() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'renderMissingPluginNotice']);
            return;
        }

        if (empty(get_option('gd_checkout_disable'))) {
            $this->loadFiles();
            add_action('wp_enqueue_scripts', [$this, 'loadScripts']);
        }

        require_once(plugin_dir_path(__FILE__) . 'inc/admin.php');
    }

    public function renderMissingPluginNotice() {
        echo '<div class="notice notice-error dismissable"><p>GoDaddy Headless Checkout requires WooCommerce to be installed and activated.</p></div>';
    }
}

new HeadlessCheckout();

/**
 * Activation hook must live outside class
 */
function gd_checkout_activation() {
    // Disable GraphiQL menu item
    $options = get_option('graphql_general_settings', []);
    if (gettype($options) !== 'array') {
        $options = [];
    }

    if (isset($options['graphiql_enabled'])) {
        $options['graphiql_enabled'] = 'off';
    } else {
        $options = array_merge($options, ['graphiql_enabled' => 'off']);
    }
    update_option('graphql_general_settings', $options);
}
register_activation_hook(__FILE__, 'GoDaddy\MWC\WordPress\gd_checkout_activation');
