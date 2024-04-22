<?php

namespace GoDaddy\MWC\WordPress\HeadlessCheckout;

if (!defined('ABSPATH')) exit;

/**
 * Class Logger
 */
class Logger {

    public static function log($message, $level = 'debug') {
        if (empty(get_option('gd_checkout_enable_logs')) || !function_exists('wc_get_logger')) {
            return;
        }

        // disable logging after 1 hour
        if (FALSE === get_option('gd_checkout_start_logs')) {
            update_option('gd_checkout_start_logs', time());
        }

        $last_log = get_option('gd_checkout_start_logs');
        if ($last_log && time() > $last_log + HOUR_IN_SECONDS) {
            // disable logging
            delete_option('gd_checkout_start_logs');
            delete_option('gd_checkout_enable_logs');
            return;
        }

        $logger = \wc_get_logger();

        if ($logger) {
            $logger->log($level, $message, ['source' => 'gd-checkout']);
        }
    }
}
