<?php
function eventpress_general_setting( $wp_customize ) {
$selective_refresh = isset( $wp_customize->selective_refresh ) ? 'postMessage' : 'refresh';
	$wp_customize->add_panel( 
		'general_settings', 
		array(
			'priority'      => 31,
			'capability'    => 'edit_theme_options',
			'title'			=> __('General', 'eventpress'),
		) 
	);
	
	// Title Seprator// 
	$wp_customize->add_section(
        'title_seprator',
        array(
        	'priority'      => 1,
            'title' 		=> __('Title Seprator','eventpress'),
			'panel'  		=> 'general_settings',
		)
    );
	
	// Seprator Hide/ Show Setting // 
	if ( class_exists( 'Eventpress_Customizer_Toggle_Control' ) ) {
		$wp_customize->add_setting( 
			'hide_show_seprator' , 
				array(
				'default' => '1',
				'capability' => 'edit_theme_options',
				'sanitize_callback' => 'eventpress_sanitize_checkbox',
			) 
		);
		
		$wp_customize->add_control( new Eventpress_Customizer_Toggle_Control( $wp_customize, 
		'hide_show_seprator', 
			array(
				'label'	      => esc_html__( 'Hide / Show', 'eventpress' ),
				'section'     => 'title_seprator',
				'type'        => 'ios', // light, ios, flat
				'priority' => 2,
			) 
		));
	}	
	
	// Seprator Icon // 
	if ( class_exists( 'Eventpress_Customizer_Icon_Picker_Control' ) ) {
		$wp_customize->add_setting(
			'seprator_icon',
			array(
				'default'			=> esc_html__('fa-bell','eventpress'),
				'sanitize_callback' => 'eventpress_sanitize_text',
				'capability' => 'edit_theme_options',
			)
		);	

		$wp_customize->add_control(new Eventpress_Customizer_Icon_Picker_Control($wp_customize,  
			'seprator_icon',
			array(
				'label'   => esc_html__('Icon','eventpress'),
				'section' => 'title_seprator',
				'iconset' => 'fa',
				'priority' => 6,
			))  
		);
	}
	/*=========================================
	Breadcrumb Section
	=========================================*/ 
	$wp_customize->add_section(
        'breadcrumb_design',
        array(
        	'priority'      => 2,
            'title' 		=> __('Breadcrumb','eventpress'),
			'panel'  		=> 'general_settings',
		)
    );
	
	$wp_customize->add_setting( 
		'hide_show_breadcrumb' , 
			array(
			'default' => '1',
			'capability'     => 'edit_theme_options',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => $selective_refresh,
		) 
	);
	
	$wp_customize->add_control( new Eventpress_Customizer_Toggle_Control( $wp_customize, 
	'hide_show_breadcrumb', 
		array(
			'label'	      => esc_html__( 'Breadcrumb Hide/Show', 'eventpress' ),
			'section'     => 'breadcrumb_design',
			'type'        => 'ios', // light, ios, flat
			'priority' => 2,
		) 
	));

	
	// Background Image // 
    $wp_customize->add_setting( 
    	'breadcrumb_background_setting' , 
    	array(
			'default' 			=> esc_url(get_template_directory_uri() .'/images/breadcrumbbg.jpg'),
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'eventpress_sanitize_url',	
		) 
	);
	
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize , 'breadcrumb_background_setting' ,
		array(
			'label'          => __( 'Background Image', 'eventpress' ),
			'section'        => 'breadcrumb_design',
			'priority' => 5,
		) 
	));
}

add_action( 'customize_register', 'eventpress_general_setting' );

// breadcrumb selective refresh
function eventpress_home_breadcrumb_section_partials( $wp_customize ){
	
	// hide show breadcrumb
	$wp_customize->selective_refresh->add_partial(
		'hide_show_breadcrumb', array(
			'selector' => '#breadcrumb-area',
			'container_inclusive' => true,
			'render_callback' => 'breadcrumb_design',
		)
	);
	}
add_action( 'customize_register', 'eventpress_home_breadcrumb_section_partials' );