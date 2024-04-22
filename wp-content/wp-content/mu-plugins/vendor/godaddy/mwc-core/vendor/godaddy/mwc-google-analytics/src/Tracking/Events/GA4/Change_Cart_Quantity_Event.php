<?php
/**
 * Google Analytics
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Google Analytics to newer
 * versions in the future. If you wish to customize Google Analytics for your
 * needs please refer to https://help.godaddy.com/help/40882 for more information.
 *
 * @author      SkyVerge
 * @copyright   Copyright (c) 2015-2024, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace GoDaddy\WordPress\MWC\GoogleAnalytics\Tracking\Events\GA4;

use GoDaddy\WordPress\MWC\GoogleAnalytics\Tracking\Adapters\Cart_Item_Event_Data_Adapter;
use GoDaddy\WordPress\MWC\GoogleAnalytics\Tracking\Events\GA4_Event;

defined( 'ABSPATH' ) or exit;

/**
 * The "change cart quantity" event.
 *
 * @since 3.0.0
 */
class Change_Cart_Quantity_Event extends GA4_Event {


	/** @var string the event ID */
	public const ID = 'change_cart_quantity';


	/**
	 * @inheritdoc
	 */
	public function get_form_field_title(): string {

		return __( 'Change Cart Quantity', 'woocommerce-google-analytics-pro' );
	}


	/**
	 * @inheritdoc
	 */
	public function get_form_field_description(): string {

		return __( 'Triggered when a customer changes the quantity of an item in the cart.', 'woocommerce-google-analytics-pro' );
	}


	/**
	 * @inheritdoc
	 */
	public function get_default_name(): string {

		return 'change_cart_quantity';
	}


	/**
	 * @inheritdoc
	 */
	public function register_hooks() : void {

		add_action( 'woocommerce_after_cart_item_quantity_update', [ $this, 'track' ], 10, 2 );
	}


	/**
	 * @inheritdoc
	 *
	 * @param string $cart_item_key the unique cart item ID
	 * @param int $quantity the changed quantity
	 */
	public function track( $cart_item_key = null, $quantity = 1 ): void {

		if ( ! $cart_item_key || empty( $item = WC()->cart->cart_contents[ $cart_item_key ] ) ) {
			return;
		}

		$this->record_via_api( [
			'category'       => 'Cart',
			'currency'       => get_woocommerce_currency(),
			'value'          => $item['line_total'],
			'items'          => [ ( new Cart_Item_Event_Data_Adapter( $item ) )->convert_from_source() ],
		] );
	}


}
