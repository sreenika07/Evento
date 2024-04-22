<?php

use GoDaddy\WordPress\MWC\Core\Admin\Notices\Notice;
use GoDaddy\WordPress\MWC\Core\Admin\Notices\Notices;

class Admin extends GoDaddy\MWC\WordPress\HeadlessCheckout {
    public function __construct() {
        $this->load();
    }

    public function load() {
        add_action('admin_enqueue_scripts', [$this, 'adminScripts']);

        add_action('customize_register', [$this, 'addCustomizerSettings']);

        add_action('plugin_action_links_' . GD_CHECKOUT_BASENAME, [$this, 'addPluginLinks']);

        add_action('admin_init', [$this, 'supportedGatewayCheck']);
    }

    public function supportedGatewayCheck() {

        // check woocommerce payment methods
        $gateways = \WC()->payment_gateways->get_available_payment_gateways();
        $unsupported = [];

        if ($gateways) {
            foreach ($gateways as $gateway) {

                if ($gateway->enabled == 'yes') {
                    $bool = in_array($gateway->id, GD_CHECKOUT_SUPPORTED_GATEWAYS);
                    if (!$bool) {
                        $unsupported[] = $gateway->method_title;
                    }
                }
            }
        }

        if ($unsupported) {
            $message = 'The following payment methods are not supported by the GoDaddy Enhanced Checkout plugin: ' . implode(', ', $unsupported) . '. Please use a <a href="https://www.godaddy.com/help/use-the-enhanced-checkout-for-woocommerce-stores-41636" target="_blank">supported payment method</a>, or deactivate the Enhanced Checkout plugin.';
            $this->displayNotice($message);
        }
    }

    public function displayNotice($message) {


        $notice = (new Notice())
            ->setId('gd-checkout-unsupported-gateways')
            ->setType(Notice::TYPE_WARNING)
            ->setRestrictedUserCapabilities(['manage_woocommerce'])
            ->setDismissible(true)
            ->setContent($message);

        Notices::enqueueAdminNotice($notice);
    }

    public function addPluginLinks($links): array {
        $links[] = '<a href="' . esc_url(admin_url('customize.php?autofocus[section]=woocommerce_checkout')) . '">' . __('Settings') . '</a>';
        $links[] = '<a href="https://www.godaddy.com/help/use-the-enhanced-checkout-for-woocommerce-stores-41636" target="_blank">' . __('Docs') . '</a>';
        return $links;
    }

    /**
     * Add checkout customizer settings (under WooCommerce section)
     */
    public function addCustomizerSettings($wp_customize): void {

        $wp_customize->add_setting('gd_checkout_heading',  []); // doesn't do anything except render the heading 

        $wp_customize->add_control(new GD_Heading_Custom_Control($wp_customize, 'gd_checkout_heading', [

            'label'   => __('GoDaddy Checkout', 'gd-checkout'),
            'description' => __('Configure the GoDaddy Checkout plugin. These settings do not apply to the default WooCommerce checkout.', 'gd-checkout'),
            'section' => 'woocommerce_checkout',

        ]));

        $wp_customize->add_setting('gd_checkout_title', [
            'default' => get_option('blogname'),
            'type' => 'option',
            'capability' => 'manage_woocommerce',
            'sanitize_callback' => 'wp_filter_nohtml_kses',
            'transport' => 'postMessage'
        ]);

        $wp_customize->add_control('gd_checkout_title', [
            'label' => __('GoDaddy Checkout', 'gd-checkout'),
            'description' => __('The title of the checkout page. (Delete the text to hide the title)', 'gd-checkout'),
            'section' => 'woocommerce_checkout',
            'settings' => 'gd_checkout_title',
            'type' => 'text',
        ]);

        $wp_customize->add_setting('gd_checkout_logo', [
            'default' => get_option('gd_checkout_logo'),
            'type' => 'option',
            'capability' => 'manage_woocommerce',
            'transport' => 'postMessage',
        ]);

        $wp_customize->add_control(new WP_Customize_Upload_Control($wp_customize, 'gd_checkout_logo', array(
            'section' => 'woocommerce_checkout',
            'label' => 'GoDaddy Checkout Logo',
            'description' => __('Upload a logo.', 'gd-checkout'),
            'mime_type' => 'image',
        )));

        $wp_customize->add_setting('gd_checkout_primary_color', [
            'default' => '#000000',
            'type' => 'option',
            'capability' => 'manage_woocommerce',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport' => 'postMessage'
        ]);

        $wp_customize->add_control('gd_checkout_primary_color', [
            'label' => __('GoDaddy Checkout Primary Color', 'gd-checkout'),
            'description' => __('The color of the header, buttons, and main accents.', 'gd-checkout'),
            'section' => 'woocommerce_checkout',
            'settings' => 'gd_checkout_primary_color',
            'type' => 'color',
        ]);

        $wp_customize->add_setting('gd_checkout_contrast_color', [
            'default' => '#FFFFFF',
            'type' => 'option',
            'capability' => 'manage_woocommerce',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport' => 'postMessage'
        ]);

        $wp_customize->add_control('gd_checkout_contrast_color', [
            'label' => __('GoDaddy Checkout Text Color', 'gd-checkout'),
            'description' => __('The color of the header and button text.', 'gd-checkout'),
            'section' => 'woocommerce_checkout',
            'settings' => 'gd_checkout_contrast_color',
            'type' => 'color',
        ]);


        $wp_customize->add_setting('gd_checkout_google_api_key', [
            'type' => 'option',
            'capability' => 'manage_woocommerce',
            'sanitize_callback' => 'wp_filter_nohtml_kses',
        ]);

        $wp_customize->add_control('gd_checkout_google_api_key', [
            'label' => __('Google Places API Key', 'gd-checkout'),
            'description' => __('To enable address autocomplete, please enter your API key. <a href="https://developers.google.com/maps/documentation/places/web-service/get-api-key" target="_blank">How to get this key</a>', 'gd-checkout'),
            'section' => 'woocommerce_checkout',
            'settings' => 'gd_checkout_google_api_key',
            'type' => 'text',
        ]);

        $wp_customize->add_setting('gd_checkout_disable', [
            'type' => 'option',
            'capability' => 'manage_woocommerce',
        ]);

        $wp_customize->add_control('gd_checkout_disable', [
            'label' => __('Disable GoDaddy Checkout', 'gd-checkout'),
            'description' => __('By disabling GoDaddy Checkout, you will revert to the regular WooCommerce checkout.', 'gd-checkout'),
            'section' => 'woocommerce_checkout',
            'settings' => 'gd_checkout_disable',
            'type' => 'checkbox',
        ]);

        $wp_customize->add_setting('gd_checkout_enable_logs', [
            'type' => 'option',
            'capability' => 'manage_woocommerce',
        ]);

        $wp_customize->add_control('gd_checkout_enable_logs', [
            'label' => __('Enable logging', 'gd-checkout'),
            'description' => __('Logs will appear in WooCommerce => Status => Logs with the prefix gd-checkout. Logs will auto-deactivate after 1 hour.', 'gd-checkout'),
            'section' => 'woocommerce_checkout',
            'settings' => 'gd_checkout_enable_logs',
            'type' => 'checkbox',
        ]);
    }

    public function adminScripts($hook_suffix) {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('gd-checkout-admin', plugins_url('../assets/js/gd-checkout-admin.js', __FILE__),  ['wp-color-picker'], false, true);
    }
}

/**
 * Add custom control for section headings (can't do this inside the class)
 */
add_action('customize_register', 'gd_checkout_custom_control');

function gd_checkout_custom_control(): void {

    class GD_Heading_Custom_Control extends WP_Customize_Control {

        //The type of control being rendered
        public $type = 'gd_section_heading';

        //Render the control in the customizer
        public function render_content() {
?>
            <div class="gd-section-heading">
                <?php if (!empty($this->label)) { ?>
                    <h2 class="gd-control-title">
                        <?php echo esc_html($this->label); ?>
                    </h2>
                    <span><?php echo esc_html($this->description); ?></span>
                <?php } ?>

            </div>
<?php
        }
    }
}

new Admin();
