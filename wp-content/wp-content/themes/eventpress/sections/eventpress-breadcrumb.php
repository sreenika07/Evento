<?php 
$eventpress_hs_breadcrumb= get_theme_mod('hide_show_breadcrumb','1');
$eventpress_bread_bg_setting= get_theme_mod('breadcrumb_background_setting',esc_url(get_template_directory_uri() .'/images/breadcrumbbg.jpg'));
if($eventpress_hs_breadcrumb == '1') :
?>
<section id="breadcrumb-area" style="background-image:url('<?php echo esc_url($eventpress_bread_bg_setting); ?> ');">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
				<h2>
					<?php 
						if ( is_day() ) : 
						
							printf( __( 'Daily Archives: %s', 'eventpress' ), get_the_date() );
						
						elseif ( is_month() ) :
						
							printf( __( 'Monthly Archives: %s', 'eventpress' ), (get_the_date( 'F Y' ) ));
							
						elseif ( is_year() ) :
						
							printf( __( 'Yearly Archives: %s', 'eventpress' ), (get_the_date( 'Y' ) ) );
							
						elseif ( is_category() ) :
						
							printf( __( 'Category Archives: %s', 'eventpress' ), (single_cat_title( '', false ) ));

						elseif ( is_tag() ) :
						
							printf( __( 'Tag Archives: %s', 'eventpress' ), (single_tag_title( '', false ) ));
							
						elseif ( is_404() ) :

							printf( __( 'Error 404', 'eventpress' ));
							
						elseif ( is_author() ) :
						
							printf( __( 'Author: %s', 'eventpress' ), (get_the_author( '', false ) ));		
						
						elseif ( is_tax( 'portfolio_categories' ) ) :

							printf( __( 'Portfolio Categories: %s', 'eventpress' ), (single_term_title( '', false ) ));	
							
						elseif ( is_tax( 'pricing_categories' ) ) :

							printf( __( 'Pricing Categories: %s', 'eventpress' ), (single_term_title( '', false ) ));	
							
						elseif ( class_exists( 'WooCommerce' ) ) : 
							
							if ( is_shop() ) {
								woocommerce_page_title();
							}
							
							elseif ( is_cart() ) {
								the_title();
							}
							
							elseif ( is_checkout() ) {
								the_title();
							}
							
							else {
								the_title();
							}
						else :
								the_title();
								
						endif;
						
					?>
				</h2>
				<?php 
					if ( function_exists( 'eventpress_title_seprator_dark' ) ) :
						eventpress_title_seprator_dark(); 
					endif;	
				?>
				 <ul class="breadcrumb-nav list-inline">
					<?php if (function_exists('eventpress_breadcrumbs')) esc_html(eventpress_breadcrumbs());?>
				</ul>
			</div>
		</div>
	</div>
</section>
<?php 
endif;
?>