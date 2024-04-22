<?php
/*
* Plugin Name:       	Evento
* Description:       	Evento plugin is provides you a complete theme demo import setup for EventPress WordPress Theme. This Plugin Developed for only EventPress & Childs Theme. EventPress is Seasonal Themes.
* Version:           	1.6
* Author: 				nayrathemes
* Author URI: 			https://nayrathemes.com
* Tested up to: 		6.5.2
* Requires: 			4.6 or higher
* License: 				GPLv3 or later
* License URI: 			http://www.gnu.org/licenses/gpl-3.0.html
* Requires PHP: 		5.6
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // don't access directly
};

define( 'EVENTO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'EVENTO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

	 
if(!function_exists('evento_activate')){
	
	function evento_activate() {	
	/**
	 * Load Custom control in Customizer
	 */
		
	if ( class_exists( 'WP_Customize_Control' ) ) {
		require_once('inc/custom-controls/range-validator/range-control.php');	
		require_once('inc/custom-controls/select/select-control.php');
	}
	
		$theme = wp_get_theme(); // gets the current theme
			if ( 'EventPress' == $theme->name){	
				 require_once('inc/eventpress/features/eventpress-slider-section.php');
				 require_once('inc/eventpress/features/eventpress-organizer.php');
				 require_once('inc/eventpress/features/eventpress-countdown.php');
				 require_once('inc/eventpress/features/eventpress-gallery.php');
				 require_once('inc/eventpress/features/eventpress-navigation.php');
				 require_once('inc/eventpress/features/eventpress-typography.php');
				 require_once('inc/eventpress/features/eventpress-style-configurator.php');
				 require_once('inc/eventpress/sections/section-slider.php');
				 require_once('inc/eventpress/sections/section-organizer.php');
				 require_once('inc/eventpress/sections/section-countdown.php');
				 require_once('inc/eventpress/sections/section-gallery.php');
				 require_once('inc/eventpress/typography_style.php');
				 require_once('inc/eventpress/prebuilt-color.php');
			}
			
			if ( 'EventPlus' == $theme->name){
				 require_once('inc/eventplus/features/eventplus-slider-section.php');
				 require_once('inc/eventplus/features/eventplus-organizer.php');
				 require_once('inc/eventplus/features/eventplus-countdown.php');
				 require_once('inc/eventplus/features/eventplus-gallery.php');
				 require_once('inc/eventplus/features/eventplus-navigation.php');
				 require_once('inc/eventplus/features/eventplus-typography.php');
				 require_once('inc/eventplus/features/eventplus-style-configurator.php');
				 require_once('inc/eventplus/sections/section-slider.php');
				 require_once('inc/eventplus/sections/section-organizer.php');
				 require_once('inc/eventplus/sections/section-countdown.php');
				 require_once('inc/eventplus/sections/section-gallery.php');
				 require_once('inc/eventplus/typography_style.php');
				 require_once('inc/eventplus/prebuilt-color.php');
			}
		}
	add_action( 'init', 'evento_activate' );
}
$theme = wp_get_theme();

//EventPress 
if ( 'EventPress' == $theme->name){	
	register_activation_hook( __FILE__, 'evento_install_function');
	if(!function_exists('evento_install_function')){
		function evento_install_function()
		{	
			$item_details_page = get_option('item_details_page'); 
			if(!$item_details_page){
				require_once('inc/eventpress/default-pages/upload-media.php');
				require_once('inc/eventpress/default-pages/home-page.php');
				require_once('inc/eventpress/default-widgets/default-widget.php');
				update_option( 'item_details_page', 'Done' );
			}
		}
	}
}
//EventPlus 
if ( 'EventPlus' == $theme->name){	
	register_activation_hook( __FILE__, 'evento_install_function');
	if(!function_exists('evento_install_function')){
		function evento_install_function()
		{	
			$item_details_page = get_option('item_details_page'); 
			if(!$item_details_page){
				require_once('inc/eventplus/default-pages/upload-media.php');
				require_once('inc/eventplus/default-pages/home-page.php');
				require_once('inc/eventplus/default-widgets/default-widget.php');
				update_option( 'item_details_page', 'Done' );
			}
		}
	}
}

//Evento Sainitize text
if(!function_exists('evento_home_page_sanitize_text')){
	function evento_home_page_sanitize_text( $input ) {
			return wp_kses_post( force_balance_tags( $input ) );
		}
}	

//Evento Widget
require_once('inc/widget/social-widget.php');			
require_once('inc/widget/info-widget.php');			
add_action("widgets_init", function(){ register_widget("evento_social_icon_widget"); });
add_action("widgets_init", function(){ register_widget("evento_info_widget"); });