<?php
class eventpress_import_dummy_data {

	private static $instance;

	public static function init( ) {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof eventpress_import_dummy_data ) ) {
			self::$instance = new eventpress_import_dummy_data;
			self::$instance->eventpress_setup_actions();
		}

	}

	/**
	 * Setup the actions used for this class.
	 */
	public function eventpress_setup_actions() {

		// Enqueue scripts
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'eventpress_import_customize_scripts' ), 0 );

	}
	
	

	public function eventpress_import_customize_scripts() {

	wp_enqueue_script( 'eventpress-import-customizer-js', get_template_directory_uri() . '/js/eventpress-import-customizer.js', array( 'customize-controls' ) );
	}
}

$eventpress_import_customizers = array(

		'import_data' => array(
			'recommended' => true,
			
		),
);
eventpress_import_dummy_data::init( apply_filters( 'eventpress_import_customizer', $eventpress_import_customizers ) );