<?php	
/**
 * The sidebar containing the main widget area.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Eventpress
 */

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
 
function eventpress_widgets_init() {
	register_sidebar( array(
		'name' => __( 'Sidebar Widget Area', 'eventpress' ),
		'id' => 'sidebar-primary',
		'description' => __( 'The Primary Widget Area', 'eventpress' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h5 class="widget-title">',
		'after_title' => '</h5>',
	) );
	
	register_sidebar( array(
		'name' => __( 'Top Bar Left Widget Area', 'eventpress' ),
		'id' => 'eventpress_top_left_widget',
		'description' => __( 'The Widget Area for Top Left Header', 'eventpress' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3><div class="title-border"></div>',
	) );

	register_sidebar( array(
		'name' => __( 'Top Bar Right  Widget Area', 'eventpress' ),
		'id' => 'eventpress_top_right_widget',
		'description' => __( 'The Widget Area for Top Right Header', 'eventpress' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3><div class="title-border"></div>',
	) );	
}
add_action( 'widgets_init', 'eventpress_widgets_init' );
 
?>