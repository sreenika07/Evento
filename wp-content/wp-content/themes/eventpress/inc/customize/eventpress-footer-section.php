<?php
function eventpress_footer( $wp_customize ) {
$selective_refresh = isset( $wp_customize->selective_refresh ) ? 'postMessage' : 'refresh';
	// Footer Panel // 
	$wp_customize->add_panel( 
		'footer_section', 
		array(
			'priority'      => 33,
			'capability'    => 'edit_theme_options',
			'title'			=> __('Footer Section', 'eventpress'),
		) 
	);
	// Footer top  Section // 
	$wp_customize->add_section(
        'footer_top',
        array(
            'title' 		=> __('Footer Top','eventpress'),
			'panel'  		=> 'footer_section',
			'priority' => 1,
		)
    );
	// footer top Hide/ Show Setting // 
	$wp_customize->add_setting( 
		'hide_show_foo_top' , 
			array(
			'default' => '1',
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'eventpress_sanitize_checkbox',
			'transport'         => $selective_refresh,
		) 
	);
	
	$wp_customize->add_control( new Eventpress_Customizer_Toggle_Control( $wp_customize, 
	'hide_show_foo_top', 
		array(
			'label'	      => esc_html__( 'Hide / Show Footer Top', 'eventpress' ),
			'section'     => 'footer_top',
			'type'        => 'ios', // light, ios, flat
			'priority' => 2,
		) 
	));
	
	$theme = wp_get_theme();
	// footer  logo // 
    $wp_customize->add_setting( 
    	'footer_logo_setting' , 
    	array(
			'default' 			=> ($theme == "eventpress")? esc_url(get_template_directory_uri() . '/images/footerlogo.png') : esc_url(get_stylesheet_directory_uri() . '/images/footerlogo.png'),
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'eventpress_sanitize_url',	
		) 
	);
	
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize , 'footer_logo_setting' ,
		array(
			'label'          => __( 'Footer Logo', 'eventpress' ),
			'section'        => 'footer_top',
			'priority' => 6,
		) 
	));
	
	// regard Setting // 
	$wp_customize->add_setting(
    	'foot_regards_text',
    	array(
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'eventpress_sanitize_html',
			'transport'         => $selective_refresh,
		)
	);	

	$wp_customize->add_control( 
		'foot_regards_text',
		array(
		    'label'   		=> __('Regards Text','eventpress'),
		    'section'		=> 'footer_top',
			'type' 			=> 'textarea',
			'priority' => 7,
		)  
	);	
	// Footer info Section // 
	$wp_customize->add_section(
        'footer_info_settings',
        array(
            'title' 		=> __('Footer Address Info','eventpress'),
			'panel'  		=> 'footer_section',
			'priority' => 2,
		)
    );
	
	// footer info Hide/ Show Setting // 
	$wp_customize->add_setting( 
		'footer_info_setting' , 
			array(
			'default' => 0,
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'eventpress_sanitize_checkbox',
			'transport'         => $selective_refresh,
		) 
	);
	
	$wp_customize->add_control( new Eventpress_Customizer_Toggle_Control( $wp_customize, 
	'footer_info_setting', 
		array(
			'label'	      => esc_html__( 'Hide / Show Address', 'eventpress' ),
			'section'     => 'footer_info_settings',
			'type'        => 'ios', // light, ios, flat
			'priority' => 2,
		) 
	));
	
	// footer info contents
	/**
	 * Customizer Repeater for add service
	 */
	
		$wp_customize->add_setting( 'footer_info_contents', 
			array(
			 'sanitize_callback' => 'Eventpress_Repeater_sanitize',
			 'transport'         => $selective_refresh
			)
		);
		
		$wp_customize->add_control( 
			new Eventpress_Repeater( $wp_customize, 
				'footer_info_contents', 
					array(
						'label'   => esc_html__('Footer Address','eventpress'),
						'section' => 'footer_info_settings',
						'add_field_label'                   => esc_html__( 'Add New Address', 'eventpress' ),
						'item_name'                         => esc_html__( 'Address', 'eventpress' ),
						'priority' => 6,
						'customizer_repeater_icon_control' => true,
						'customizer_repeater_title_control' => true,
						'customizer_repeater_subtitle_control' => true,
					) 
				) 
			);
	// Footer Setting Section // 
	$wp_customize->add_section(
        'footer_copyright',
        array(
            'title' 		=> __('Copyright Content','eventpress'),
			'panel'  		=> 'footer_section',
			'priority' => 10,
		)
    );
	// Copyright Content Hide/Show Setting // 
	// Team Hide/ Show Setting // 
	$wp_customize->add_setting( 
		'hide_show_copyright' , 
			array(
			'default' => '1',
			'capability' => 'edit_theme_options',
			'sanitize_callback' => 'eventpress_sanitize_checkbox',
			'transport'         => $selective_refresh,
		) 
	);
	
	$wp_customize->add_control( new Eventpress_Customizer_Toggle_Control( $wp_customize, 
	'hide_show_copyright', 
		array(
			'label'	      => esc_html__( 'Hide / Show Section', 'eventpress' ),
			'section'     => 'footer_copyright',
			'type'        => 'ios', // light, ios, flat
			'priority' => 2,
		) 
	));

	// Copyright Content Setting // 
	$eventpress_footer_copyright = esc_html__('Copyright &copy; [current_year] [site_title] | Powered by [theme_author]', 'eventpress' );
	$wp_customize->add_setting(
    	'copyright_content',
    	array(
			'default' => $eventpress_footer_copyright,
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'wp_kses_post',
		)
	);	

	$wp_customize->add_control( 
		'copyright_content',
		array(
		    'label'   		=> __('Copyright Content','eventpress'),
		    'section'		=> 'footer_copyright',
			'type' 			=> 'textarea',
			'priority' => 6,
		)  
	);

	// Background Image // 
    $wp_customize->add_setting( 
    	'footer_background_setting' , 
    	array(
			'default' 			=> esc_url(get_template_directory_uri() . '/images/footerbg.jpg'),
			'capability'     	=> 'edit_theme_options',
			'sanitize_callback' => 'eventpress_sanitize_url',
		) 
	);
	
	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize , 'footer_background_setting' ,
		array(
			'label'          => __( 'Set Background Image', 'eventpress' ),
			'section'        => 'footer_copyright',
			'priority' => 12,
		) 
	));	
}
add_action( 'customize_register', 'eventpress_footer' );

// footer selective refresh
function eventpress_home_footer_section_partials( $wp_customize ){

	// hide_show_foo_top
	$wp_customize->selective_refresh->add_partial(
		'hide_show_foo_top', array(
			'selector' => '.footer-section #foot-top',
			'container_inclusive' => true,
			'render_callback' => 'footer_top',
			'fallback_refresh' => true,
		)
	);
	
	// hide_show_copyright
	$wp_customize->selective_refresh->add_partial(
		'hide_show_copyright', array(
			'selector' => '.footer-section .footer-copyright',
			'container_inclusive' => true,
			'render_callback' => 'footer_copyright',
			'fallback_refresh' => true,
		)
	);
	
	// hide_show_footer_social
	$wp_customize->selective_refresh->add_partial(
		'footer_info_setting', array(
			'selector' => '#foo-co-in',
			'container_inclusive' => true,
			'render_callback' => 'footer_info_settings',
			'fallback_refresh' => true,
		)
	);
	// copyright_content
	$wp_customize->selective_refresh->add_partial( 'copyright_content', array(
		'selector'            => '.footer-section .footer-copyright p',
		'settings'            => 'copyright_content',
		'render_callback'  => 'eventpress_copyright_render_callback',
	
	) );
	
	// foot_regards_text
	$wp_customize->selective_refresh->add_partial( 'foot_regards_text', array(
		'selector'            => '.footer-section .footer-logo h2',
		'settings'            => 'foot_regards_text',
		'render_callback'  => 'eventpress_foot_regards_text_render_callback',
	
	) );
	// footer_info_contents
	$wp_customize->selective_refresh->add_partial( 'footer_info_contents', array(
		'selector'            => '#foo-co-in',
	
	) );
	}

add_action( 'customize_register', 'eventpress_home_footer_section_partials' );

// social icons
function eventpress_copyright_render_callback() {
	return get_theme_mod( 'copyright_content' );
}
// foot_regards_text
function eventpress_foot_regards_text_render_callback() {
	return get_theme_mod( 'foot_regards_text' );
}