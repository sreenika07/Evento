<?php


class Frontend extends GoDaddy\MWC\WordPress\HeadlessCheckout {
    public function __construct() {
        $this->load();
    }

    public function load() {
        add_action('wp_footer', [$this, 'renderCheckoutIframe']);
        add_action('init', [$this, 'initActions']);

        $this->customizeCheckoutButtons();
    }

    public function initActions(): void {
        // if the checkout can't load, disable it to show default woo checkout. This happens when the user clicks a button shown during a fatal checkout error.
        if (isset($_GET['gd-disable-checkout'])) {
            setcookie("gd-disable-checkout", true, time() + 3600);
        }

        if (!defined("GD_CHECKOUT_LOCAL_SCRIPTS")) {
            $this->modifyIndexHtml();
        }
    }

    // if logging is enabled in the customizer, or if the constant is defined, enable debug mode
    public function isDebugMode() {
        if (!empty(get_option('gd_checkout_enable_logs')) || (defined('GD_CHECKOUT_DEBUG') && GD_CHECKOUT_DEBUG === true)) {
            return true;
        }

        return false;
    }

    /**
     *  Add google api key and cache bust params to iframe index.html 
     */
    public function modifyIndexHtml(): void {
        $google_api_key = get_option('gd_checkout_google_api_key');
        $str = file_get_contents(GD_CHECKOUT_DIR . 'assets/template-index.html');

        if ($google_api_key) {
            $str = str_replace("<!-- GOOGLE SCRIPTS PLACEHOLDER DO NOT DELETE -->", '<script src="https://maps.googleapis.com/maps/api/js?key=' . $google_api_key . '&libraries=places&callback=initMap" async></script>', $str);
        }

        $cache_bust = $this->getCacheBustParam();

        // if debug is enabled load staging script (most recent copy with logs, etc)
        if ($this->isDebugMode()) {
            $str = str_replace("index.js?cache-bust", "staging/index.js?cache-bust=" . $cache_bust, $str);
        } else {
            $str = str_replace("index.js?cache-bust", GD_CHECKOUT_SCRIPT_VERSION . '/index.js?cache-bust=' . $cache_bust, $str);
        }

        file_put_contents(GD_CHECKOUT_DIR . 'dist/index.html', $str);
    }

    /**
     * Replace default woocommerce checkout buttons with one that opens the checkout modal
     */
    public function customizeCheckoutButtons(): void {

        add_action('woocommerce_after_add_to_cart_form', [$this, 'renderCheckoutButton']);
        add_filter('woocommerce_locate_template', [$this, 'filterTemplatePath'], 10, 3);
        remove_action('woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20);
        add_action('woocommerce_widget_shopping_cart_buttons', [$this, 'replaceCartCheckoutBtn'], 20);
    }

    public function replaceCartCheckoutBtn(): void {
        echo '<a href="' . wc_get_checkout_url() . '" class="button checkout gd-checkout-open wc-forward">' . esc_html__('Checkout', 'woocommerce') . '</a>';
    }

    public function getCacheBustParam() {

        if (defined('GD_CHECKOUT_DEBUG') && GD_CHECKOUT_DEBUG === true) {
            $cache_bust = time();
        } elseif (get_transient('gd_checkout_cache_bust')) {
            $cache_bust = get_transient('gd_checkout_cache_bust');
        } else {
            $cache_bust = time();
            set_transient('gd_checkout_cache_bust', $cache_bust, DAY_IN_SECONDS);
        }

        return $cache_bust;
    }

    /**
     * Add the checkout iframe/modal to woo pages
     */
    public function renderCheckoutIframe(): void {

        if (!self::isCheckoutPage()) return;

        $customizer_param = is_customize_preview() ? '?is_customizer=1' : '';

        $show_checkout = apply_filters('gd_checkout_load', true);
        $checkout_disabled = isset($_COOKIE['gd-disable-checkout']) || isset($_GET['gd-disable-checkout']) && $_GET['gd-disable-checkout'] === '1' ? true : false;
        $is_checkout = is_checkout() && !$checkout_disabled && strpos($_SERVER['REQUEST_URI'], 'order-received') === false ? true : false;

        $cache_bust = $this->getCacheBustParam();

        if ($show_checkout) {
            echo '<iframe loading="lazy" id="gd-checkout" class="' . ($is_checkout ? 'checkout-open' : '') . '" src="' . GD_CHECKOUT_URL . "dist/index.html" . $customizer_param . "?cache-bust=" . $cache_bust . '" frameborder="0"></iframe>';
        }
    }

    public static function renderCheckoutButton(): void {

        if (!self::isCheckoutPage()) return;

        $checkout_button_markup = apply_filters('gd_checkout_button_markup', '<button class="button gd-checkout-open gd-checkout-button">Checkout</button>');
        if (!WC()->cart->is_empty()) {
            echo $checkout_button_markup;
        }
    }

    /** 
     * Replace woocommerce templates with our own
     */
    public function filterTemplatePath($template, $template_name, $template_path) {

        global $woocommerce;

        $_template = $template;

        if (!$template_path) $template_path = $woocommerce->template_url;

        $plugin_path  = untrailingslashit(plugin_dir_path(__FILE__)) . '/templates/';

        // Look within passed path within the theme

        $template = locate_template(

            array(

                $template_path . $template_name,

                $template_name

            )

        );

        // Modification: Get the template from this plugin, if it exists

        if (file_exists($plugin_path . $template_name))

            $template = $plugin_path . $template_name;



        // Use default template

        if (!$template)

            $template = $_template;



        // Return what we found

        return $template;
    }
}

new Frontend();
