<?php
function eventplus_style_configurator( $wp_customize ) {

	/*=========================================
	Style Configurator Settings Section
	=========================================*/
	$wp_customize->add_panel( 
		'style_configurator', 
		array(
			'priority'      => 35,
			'capability'    => 'edit_theme_options',
			'title'			=> __('Style Configurator', 'eventplus'),
		) 
	);

	/*=========================================
	Pre Built Colors Settings Section
	=========================================*/
	// Footer Setting Section // 
	$wp_customize->add_section(
        'prebuilt_colors',
        array(
            'title' 		=> __('Prebuilt Theme Color','eventplus'),
            'description' 	=>'',
			'panel'  		=> 'style_configurator',
		)
    );
	
	//Pre Built Colors
	class WP_color_Customize_Control extends WP_Customize_Control {
	public $type = 'new_menu';

		   function render_content()
		   {
		   echo '<h3>' .  __( 'Select Your Prebuilt Theme Color', 'eventplus' ) . '</h3>';
			  $name = '_customize-color-radio-' . $this->id; 
			  foreach($this->choices as $key => $value ) {
				?>
				   <label>
					<input type="radio" value="<?php echo $key; ?>" name="<?php echo esc_attr( $name ); ?>" data-customize-setting-link="<?php echo esc_attr( $this->id ); ?>" <?php if($this->value() == $key){ echo 'checked'; } ?>>
					<img <?php if($this->value() == $key){ echo 'class="selected_img"'; } ?> src="<?php echo EVENTO_PLUGIN_URL  ?>inc/eventplus/images/color/<?php echo $value; ?>" alt="<?php echo esc_attr( $value ); ?>" />
					</label>
					
				<?php
			  } ?>
			
			  <script>
				jQuery(document).ready(function($) {
					$("#customize-control-theme_color label img").click(function(){
						$("#customize-control-theme_color label img").removeClass("selected_img");
						$(this).addClass("selected_img");
					});
				});
			  </script>
			  <?php 
		   }

	}
	
	 //Theme Color Scheme
	$wp_customize->add_setting(
	'theme_color', array(
	'default' => '#FF9C00',  
	'capability'     => 'edit_theme_options',
    ));
	$wp_customize->add_control(new WP_color_Customize_Control($wp_customize,'theme_color',
	array(
        'label'   => __('Select Your Theme Color', 'eventplus'),
        'section' => 'prebuilt_colors',
		'type' => 'radio',
		'settings' => 'theme_color',	
		'choices' => array(
			'#FF9C00' => 'default.png',
            '#0574f7' => '1.png',
			'#f24259' => '2.png',
    )
	
	)));
}

add_action( 'customize_register', 'eventplus_style_configurator' );
?>