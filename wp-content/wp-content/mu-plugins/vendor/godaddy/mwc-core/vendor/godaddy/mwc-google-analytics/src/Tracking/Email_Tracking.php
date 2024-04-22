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

namespace GoDaddy\WordPress\MWC\GoogleAnalytics\Tracking;

use GoDaddy\WordPress\MWC\GoogleAnalytics\Helpers\Identity_Helper;
use GoDaddy\WordPress\MWC\GoogleAnalytics\Tracking;
use SkyVerge\WooCommerce\PluginFramework\v5_11_0\SV_WC_Order_Compatibility;
use function GoDaddy\WordPress\MWC\GoogleAnalytics\wc_google_analytics_pro;

defined( 'ABSPATH' ) or exit;

/**
 * The email tracking class.
 *
 * @since 1.0.0
 */
class Email_Tracking {


	/** @var \WC_Email[] email instances that should be tracked **/
	private array $emails;


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'init', [ $this, 'init' ] );
	}


	/**
	 * Initializes the email tracker.
	 *
	 * Loading emails classes on `init` is required to support custom emails defined by external extensions/code.
	 *
	 * @internal
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public function init(): void {

		foreach ( $this->get_emails() as $tracked_email ) {

			// add filters for additional content for all tracked emails
			add_filter( 'woocommerce_email_additional_content_' . $tracked_email->id, [ $this, 'track_opens' ], 10, 2 );
		}
	}


	/**
	 * Gets the emails that should be tracked.
	 *
	 * @since 1.0.0
	 *
	 * @return array associative array of \WC_Emails
	 */
	public function get_emails(): array {

		if ( ! isset( $this->emails ) ) {

			$all_emails   = WC()->mailer()->get_emails();
			$track_emails = [];

			// only track customer emails
			if ( ! empty( $all_emails ) ) {

				$track_emails = array_filter( $all_emails, static function ( $email ) {

					return 0 === strpos( $email->id, 'customer_' );
				} );
			}

			/**
			 * Filter which emails should be tracked
			 *
			 * By default, only customer emails are tracked.
			 *
			 * @since 1.0.0
			 *
			 * @param array $track_emails associative array of emails to be tracked
			 */
			$this->emails = apply_filters( 'wc_google_analytics_pro_track_emails', $track_emails );
		}

		return $this->emails;
	}


	/**
	 * Gets an email based on its ID.
	 *
	 * @since 1.8.1
	 *
	 * @param string $email_id the email ID
	 * @return \WC_Email|null
	 */
	private function get_email_by_id( string $email_id ): ?\WC_Email {

		$found_email = null;

		foreach ( $this->get_emails() as $email ) {

			if ( $email_id === $email->id ) {

				$found_email = $email;
				break;
			}
		}

		return $found_email;
	}


	/** Tracking methods ************************************************/


	/**
	 * Adds the tracking image to the email HTML content.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @param string $content content to show below main email content
	 * @param mixed $object object this email is for, for example an order or customer
	 *
	 * @return string
	 */
	public function track_opens( string $content, $object ): string {

		// get the integration class instance
		$integration = wc_google_analytics_pro()->get_integration();

		$tracking_id    = Tracking::get_tracking_id();
		$measurement_id = Tracking::get_measurement_id();

		// skip if no tracking ID
		if ( ! $tracking_id || ! $measurement_id ) {
			return $content;
		}

		$email_id = str_replace( 'woocommerce_email_additional_content_', '', current_filter() );
		$email    = $this->get_email_by_id( $email_id );

		// skip if we're not tracking this email
		if ( ! $email ) {
			return $content;
		}

		// skip if plain email
		if ( 'html' !== ( ! empty( $email->settings['email_type'] ) ? $email->settings['email_type'] : null ) ) {
			return $content;
		}

		$cid = $uid = null;

		if ( $object instanceof \WC_Order ) {

			$order = $object;

			// try to get client & user ID from order
			$cid = SV_WC_Order_Compatibility::get_order_meta( $order, '_wc_google_analytics_pro_identity' );
			$uid = $order->get_customer_id();

		} elseif ( $object instanceof \WP_User ) {

			$user = $object;

			// try to get client & user ID from user data
			$uid = $user->ID;
			$cid = get_user_meta( $user->ID, '_wc_google_analytics_pro_identity', true );
		}

		// fall back to generating UUID

		// skip tracking email open if not enabled for the user's role
		if ( null !== $uid && ! Tracking::is_tracking_enabled_for_user_role( $uid ) ) {
			return $content;
		}

		$track_user_id = 'yes' === $integration->get_option( 'track_user_id' );

		// by default, a UUID will only be generated if we have no CID, we have a user id and user-id tracking is enabled
		// note: when changing this logic here, adjust the logic in Identity_Helper::get_cid() as well
		$generate_uuid = ! $cid && $uid && $track_user_id;

		/** This filter is documented in src/class-wc-google-analytics-pro-integration.php */
		$generate_uuid = apply_filters( 'wc_google_analytics_pro_generate_client_id', $generate_uuid );

		if ( $generate_uuid ) {
			$cid = Identity_Helper::generate_uuid();
		}

		// bail out if tracking user ID is enabled, and we don't have a proper user ID nor client ID (registered users/guests)
		// or tracking user ID is disabled, and we don't have proper CID
		if ( ( ! $track_user_id && ! $cid ) || ( $track_user_id && ! $cid && ! $uid ) ) {
			return $content;
		}

		return implode('', [
			$content,
			$this->get_tracking_image( $measurement_id, $cid, $uid, $email ),
			$this->get_ua_tracking_image( $tracking_id, $cid, $uid, $email ),
		]);
	}


	/**
	 * Gets the GA4 tracking image.
	 *
	 * @since 3.0.0
	 *
	 * @param string $measurement_id
	 * @param $cid
	 * @param int|null $uid
	 * @param \WC_Email $email
	 * @return string
	 */
	protected function get_tracking_image( string $measurement_id, $cid, ?int $uid, \WC_Email $email ): string {

		$url   = 'https://www.google-analytics.com/collect?';
		$query = urldecode(http_build_query([
			'v'             => 2,
			'tid'           => $measurement_id,            // Measurement ID. Required
			'cid'           => $cid,                       // Client (anonymous) ID. Required
			'uid'           => $uid,                       // User ID
			'en'            => 'open_email',               // Event Name
			'ep.category'   => 'Emails',                   // Event Category
			'ep.email_name' => urlencode( $email->title ), // Event Category
		], '', '&'));

		return sprintf( '<img src="%s" alt="" />', $url . $query );
	}


	/**
	 * Gets the Universal Analytics tracking image.
	 *
	 * @since 3.0.0
	 *
	 * @param string $tracking_id
	 * @param $cid
	 * @param int|null $uid
	 * @param \WC_Email $email
	 * @return string
	 */
	protected function get_ua_tracking_image( string $tracking_id, $cid, ?int $uid, \WC_Email $email ): string {

		$url   = 'https://www.google-analytics.com/collect?';
		$query = urldecode(http_build_query([
			'v'   => 1,
			'tid' => $tracking_id,                                              // Tracking ID. Required
			'cid' => $cid,                                                      // Client (anonymous) ID. Required
			'uid' => $uid,                                                      // User ID
			't'   => 'event',                                                   // Tracking an event
			'ec'  => 'Emails',                                                  // Event Category
			'ea'  => 'open',                                                    // Event Action
			'el'  => urlencode( $email->title ),                                // Event Label - email title
			'dp'  => urlencode( '/emails/' . sanitize_title( $email->title ) ), // Document Path. Unique for each email
			'dt'  => urlencode( $email->title ),                                // Document Title - email title
		], '', '&'));

		return sprintf( '<img src="%s" alt="" />', $url . $query );
	}


}
