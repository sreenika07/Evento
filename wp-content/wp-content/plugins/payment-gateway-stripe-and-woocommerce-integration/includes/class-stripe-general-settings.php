<?php

if (!defined('ABSPATH')) {
    exit;
}  

/**
 * EH_Stripe_General_Settings class.
 *
 * @extends EH_Stripe_Payment
 */
class EH_Stripe_General_Settings extends EH_Stripe_Payment {

    
    public function __construct() {
		$this->id        = 'eh_stripe_pay';
        $this->init_form_fields();
        $this->init_settings();
	}

    public function init_form_fields() {

        $log_file_success = '';
        $log_file_failed = '';
        if ( function_exists( 'wp_hash' ) ) {
            $date_suffix = date( 'Y-m-d', time() );
            
            $handle_success = 'eh_stripe_pay_live';
            $hash_suffix_success = wp_hash( $handle_success );
            $log_file_success = sanitize_file_name( implode( '-', array( $handle_success, $date_suffix, $hash_suffix_success ) ) . '.log' );

            $handle_failed = 'eh_stripe_pay_dead';
            $hash_suffix_failed = wp_hash( $handle_failed );
            $log_file_failed = sanitize_file_name( implode( '-', array( $handle_failed, $date_suffix, $hash_suffix_failed ) ) . '.log' );
        }

        $url = add_query_arg( 'wc-api', 'wt_stripe', trailingslashit( get_home_url() ) );
        $this->form_fields = array(
            'eh_stripe_prerequesties' => array(
                'type' => 'title',
                'description' => sprintf("<div class='wt_info_div'><p><b>".__( 'Pre-requisites:','payment-gateway-stripe-and-woocommerce-integration' )."</b></p> <ul class='wt_notice_bar_style'><li>".__( 'To know the countries that support Stripe, please view the  <a href="https://stripe.com/global" target="_blank">country list.</a>','payment-gateway-stripe-and-woocommerce-integration' )."</li> <li>".__( 'Get the API keys from  <a href="https://dashboard.stripe.com/dashboard" target="_blank"> Stripe dashboard </a> and insert keys in Stripe credential fields.','payment-gateway-stripe-and-woocommerce-integration' )."</li><li>".__( 'In live mode, an SSL certificate must be installed on your site to use Stripe.','payment-gateway-stripe-and-woocommerce-integration' )."</li></ul></div><p><a target='_blank' href='https://www.webtoffee.com/woocommerce-stripe-payment-gateway-plugin-user-guide/'>  ".__('Read documentation', 'payment-gateway-stripe-and-woocommerce-integration')." </a></p>"),
            ),
            'eh_stripe_credit_title' => array(
                'class'=> 'eh-css-class',
                'title' => sprintf('<span style="font-weight: bold; font-size: 15px; color:#23282d;font-size:15px;">'.__( 'Stripe Credentials','payment-gateway-stripe-and-woocommerce-integration' ).'<span>'),
                'type' => 'title',
                
            ),
            'eh_stripe_mode' => array(
                'title' => __('Transaction mode', 'payment-gateway-stripe-and-woocommerce-integration'),
                'type' => 'select',
                'options' => array(
                    'test' => __('Test mode', 'payment-gateway-stripe-and-woocommerce-integration'),
                    'live' => __('Live mode', 'payment-gateway-stripe-and-woocommerce-integration')
                ),
                'class' => 'wc-enhanced-select',
                'default' => 'test',
                'desc_tip' => __('Choose test mode to trial run using test API keys. Switch to live mode to begin accepting payments with Stripe using live API keys.', 'payment-gateway-stripe-and-woocommerce-integration')
            ),
            'eh_stripe_test_publishable_key' => array(
                'title' => __('Test publishable key', 'payment-gateway-stripe-and-woocommerce-integration'),
                'type' => 'text',
                'description' => __('Get the test publishable key from your stripe account.', 'payment-gateway-stripe-and-woocommerce-integration'),
                'placeholder' => 'Test publishable key',
                'desc_tip' => true
            ),
            'eh_stripe_test_secret_key' => array(
                'title' => __('Test secret key', 'payment-gateway-stripe-and-woocommerce-integration'),
                'type' => 'password',
                'description' => __('Get the test secret key from your stripe account.', 'payment-gateway-stripe-and-woocommerce-integration'),
                'placeholder' => 'Test secret key',
                'default'     => '',
                'desc_tip' => true
            ),
            'eh_stripe_live_publishable_key' => array(
                'title' => __('Live publishable key', 'payment-gateway-stripe-and-woocommerce-integration'),
                'type' => 'text',
                'description' => __('Get the live publishable key from your stripe account.', 'payment-gateway-stripe-and-woocommerce-integration'),
                'placeholder' => 'Live publishable key',
                'desc_tip' => true
            ),
            'eh_stripe_live_secret_key' => array(
                'title' => __('Live secret key', 'payment-gateway-stripe-and-woocommerce-integration'),
                'type' => 'password',
                'description' => __('Get the live secret key from your stripe account.', 'payment-gateway-stripe-and-woocommerce-integration'),
                'placeholder' => 'Live secret key',
                'default'     => '',
                'desc_tip' => true
            ),
            'eh_stripe_overview_title' => array(
                'class'=> 'eh-css-class',
                'type' => 'title',
            ),
            'overview' => array(
                'title' => __('Stripe overview page', 'payment-gateway-stripe-and-woocommerce-integration'),
                'label' => __('Enable', 'payment-gateway-stripe-and-woocommerce-integration'),
                'type' => 'checkbox',
                'description' => __('Enable to have a sub menu ‘Stripe Overview’ that replicates Stripe dashboard. Gives provision to manage orders, process partial/full refunds and capture payments.', 'payment-gateway-stripe-and-woocommerce-integration'),
                'default' => 'no',
            ),
            'eh_stripe_capture' => array(
                'title' => __('Capture payment immediately', 'payment-gateway-stripe-and-woocommerce-integration'),
                'label' => __('Enable', 'payment-gateway-stripe-and-woocommerce-integration'),
                'type' => 'checkbox',
                'description' => __('Disable to capture payments later manually from Stripe dashboard/overview/order details page. Uncaptured payment will expire in 7 days. <br><br>Alipay, WeChat Pay, Sofort, iDEAL and SEPA Payment does not allow to manually capture payments later.', 'payment-gateway-stripe-and-woocommerce-integration'),
                'default' => 'yes',
                
            ),
            'eh_stripe_webhhook' => array(
              'class'=> 'eh-css-class',
              'title' => sprintf('<span style="font-weight: bold; font-size: 15px; color:#23282d;">'.__( 'Webhooks','payment-gateway-stripe-and-woocommerce-integration' ).'<span>'),
              'type' => 'title',
              'description' => sprintf(__('Stripe uses webhooks to notify charge statuses and update order statuses for payment method like SEPA Direct Debit, Klarna, Bacs Direct Debit, BECS Direct Debit, Boleto, OXXO, Sofort, Stripe Checkout.%1$sTo confgure, add the following webhook endpoint %2$s %3$s %4$s to your <a href="https://dashboard.stripe.com/account/webhooks" target="_blank">Stripe account (Dashboard > Developers > Webhooks > Add endpoint)</a>%5$s', 'payment-gateway-stripe-and-woocommerce-integration'), '<p>', '<br><b>', $url, '</b>', '</p>'),
             ),
            'eh_stripe_webhook_secret' => array(
                'title' => __('Webhook secret', 'payment-gateway-stripe-and-woocommerce-integration'),
                'type' => 'text',
                'description' => __('Webhook secret to ensure the requests are coming from Stripe.', 'payment-gateway-stripe-and-woocommerce-integration'),
                'placeholder' => 'Webhook secret',
                'desc_tip' => true
            ),            
          'eh_stripe_log_title' => array(
                'title' => sprintf('<span style="font-weight: bold; font-size: 15px; color:#23282d;">'.__( 'Debug','payment-gateway-stripe-and-woocommerce-integration' ).'<span>'),
                'type' => 'title',
                'class'=> 'eh-css-class',
                'description' => sprintf(__('Records Stripe payment transactions into WooCommerce status log. <a href="' . admin_url("admin.php?page=wc-status&tab=logs") . '" target="_blank"> View log </a>', 'payment-gateway-stripe-and-woocommerce-integration')),
            ),
            'eh_stripe_logging' => array(
                'title' => __('Log', 'payment-gateway-stripe-and-woocommerce-integration'),
                'label' => __('Enable', 'payment-gateway-stripe-and-woocommerce-integration'),
                'type' => 'checkbox',
                'description' => sprintf('<span style="color:green">'.__( 'Success log file','payment-gateway-stripe-and-woocommerce-integration' ).'</span>: ' . $log_file_success . ' <br> <br><span style="color:red">'.__( 'Failure log file','payment-gateway-stripe-and-woocommerce-integration' ).'</span >: ' . $log_file_failed),
                'default' => 'yes',
                'desc_tip' => __('Enable to record stripe payment transaction in a log file.', 'payment-gateway-stripe-and-woocommerce-integration')
            )
           
        );
        
    }
    public function admin_options() {
       
        parent::admin_options();
    }

    public function process_admin_options(){
        
        parent::process_admin_options();
    }

    //gets file size of log files in units of bytes
    public function file_size($bytes) {
        $result = 0;
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach ($arBytes as $arItem) {
            if ($bytes >= $arItem["VALUE"]) {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", ".", strval(round($result, 2))) . " " . $arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

}