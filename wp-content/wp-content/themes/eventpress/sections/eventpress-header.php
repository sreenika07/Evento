<?php 
	$eventpress_booknow_setting			= get_theme_mod('booknow_setting','1'); 
	$eventpress_hdr_btn_icon			= get_theme_mod('header_btn_icon','fa-bell'); 
	$eventpress_hdr_btn_lbl				= get_theme_mod('header_btn_lbl'); 
	$eventpress_hdr_btn_link			= get_theme_mod('header_btn_link'); 
?>
<!--===================== 
        Start: Navbar
     =====================-->
	<?php if ( get_header_image() ) : ?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" id="custom-header" rel="home">
			<img src="<?php esc_url(header_image()); ?>" width="<?php echo esc_attr( get_custom_header()->width ); ?>" height="<?php echo esc_attr( get_custom_header()->height ); ?>" alt="<?php echo esc_attr(get_bloginfo( 'title' )); ?>">
		</a>
	<?php endif;  ?>
     <div class="navbar-wrapper multipage <?php echo esc_attr(eventpress_sticky_menu()); ?>">
         <nav class="navbar navbar-expand-lg navbar-default">
            <div class="container">
				<div class="logo-bbc">
					<!-- LOGO -->
						<?php
							if(has_custom_logo())
							{	
								the_custom_logo();
							}
							else { 
							?>
							<a class="navbar-brand logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
								<p class="site-title"><?php echo esc_html(get_bloginfo('name')); ?></p>	
							</a>    	
						<?php 						
							}
						?>
						<?php
							$eventpress_site_desc = get_bloginfo( 'description');
							if ($eventpress_site_desc) : ?>
								<p class="site-description"><?php echo esc_html($eventpress_site_desc); ?></p>
						<?php endif; ?>
                </div>
				<div class="d-none d-lg-block navbar-flex" id="navbarCollapse">
                   <?php 
					wp_nav_menu( 
						array(  
							'theme_location' => 'primary_menu',
							'container'  => '',
							'menu_class' => 'navbar-nav ml-auto',
							'fallback_cb' => 'WP_Bootstrap_Navwalker::fallback',
							'walker' => new WP_Bootstrap_Navwalker()
							 ) 
						);
					?>					
                </div>
				<?php if($eventpress_booknow_setting == '1') { ?>
					<div class="d-none d-lg-block" id="navbarCollapse">
						<ul class="nav-left">
							<?php if ( ! empty( $eventpress_hdr_btn_lbl ) ) : ?>
								<li><a href="<?php echo esc_url( $eventpress_hdr_btn_link ); ?>" class="hover-effect2 nav-btn"><?php echo esc_html( $eventpress_hdr_btn_lbl ); ?><i class="fa <?php echo esc_attr( $eventpress_hdr_btn_icon ); ?>"></i></a></li>
							<?php endif; ?>	
						</ul>
					</div>
				<?php } ?>
                
            </div>        
        </nav>

        <!-- Start Mobile Menu -->
        <div class="mobile-menu-area d-lg-none">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="mobile-menu">
                            <nav class="mobile-menu-active">
                               <?php 
								wp_nav_menu( 
									array(  
										'theme_location' => 'primary_menu',
										'container'  => '',
										'menu_class' => '',
										'fallback_cb' => 'WP_Bootstrap_Navwalker::fallback',
										'walker' => new WP_Bootstrap_Navwalker()
										 ) 
									);
								?>
                            </nav>                            
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- End Mobile Menu -->
	</div>
    <!--===================== 
        End: Navbar
     =====================-->
	 
	 <!-- Start: Sidenav
    ============================= -->

    <div class="sidenav cart ">
        <div class="sidenav-header">
            <h3><?php esc_html_e('Your cart','eventpress'); ?></h3>
            <span class="fa fa-times close-sidenav"></span>
        </div>
        <?php if ( class_exists( 'WooCommerce' ) ) { ?>
				<?php get_template_part('woocommerce/cart/mini','cart'); ?>
		<?php } ?>
    </div>
    <span class="cart-overlay"></span>

    <!-- End: Sidenav
    ============================= -->

    
    <!--===// Start:  Search
    =================================-->
    <div class="search__area">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="search__inner">
                        <form action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" id="searchform" class="search-form" <?php get_search_form(false); ?>>
                            <input type="search" name="s" id="s" value="" placeholder="Search" class="" aria-label="Search">
                            <button type="submit" class="search-submit"><i class="fa fa-search"></i></button>
                        </form>
                        <div class="search__close__btn">
                            <span class="search__close__btn_icon"><i class="fa fa-times"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--===// End: Search
    =================================-->
<?php 
	if ( !is_page_template( 'templates/template-homepage.php' ) ) {
			eventpress_breadcrumbs_style(); 
		}
?>