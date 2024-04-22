<?php 
/**
Template Name: Homepage One
*/
?>
<?php 
	get_header();
	do_action( 'eventpress_sections', false );
	get_template_part('sections/section','blog');
	get_footer();
?>