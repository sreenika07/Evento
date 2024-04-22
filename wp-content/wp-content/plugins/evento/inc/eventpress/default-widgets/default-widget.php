<?php
	$MediaId = get_option('eventpress_media_id');
	set_theme_mod( 'custom_logo', $MediaId[0] );
	set_theme_mod('header_btn_lbl','Book Now');
?>