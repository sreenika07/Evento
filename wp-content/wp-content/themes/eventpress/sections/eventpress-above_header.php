<?php
	$hide_show_right_icon		= get_theme_mod('hide_show_right_icon','1');
	$header_music	            = get_theme_mod('header_music');
?>
<section id="header-top" class="header-top-bg">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-12 text-lg-left text-center header-left mb-lg-0 mb-1 d-none d-lg-block">
                	<div class="eventpress-top-left">
                    	<?php dynamic_sidebar( 'eventpress_top_left_widget' ); ?>
                    </div>
                </div>
				<?php if($hide_show_right_icon == '1') { ?>
                <div class="col-lg-6 text-center text-md-right header-right d-none d-lg-block">
                	<div class="eventpress-top-right">
                		<?php dynamic_sidebar( 'eventpress_top_right_widget' ); ?>
                	</div>
                    <ul>
						<?php if ( class_exists( 'WooCommerce' ) ) { ?>
                        <li>
                            <div class="cart-icon-wrapper cart--open">
                                <i class="fa fa-shopping-bag"></i>
									<?php 
										if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
											$count = WC()->cart->cart_contents_count;
											$cart_url = wc_get_cart_url();
											
											if ( $count > 0 ) {
											?>
												 <span class="badge"><?php echo esc_html( $count ); ?></span>
											<?php 
											}
											else {
												?>
												<span class="badge">0</span>
												<?php 
											}
										}
									?>
                            </div>
                        </li>
						<?php } ?>
                        <li><a href="#" class="search__open"><i class="fa fa-search"></i></a></li> 
						<?php if ( ! empty( $header_music ) ) : ?>
						<li class="play-pause">
							<a href="javascript:void(0)" title="Play video" class="play"><i class="fa fa-play"></i></a>
							<a href="javascript:void(0)" title="Pause video" class="pause"><i class="fa fa-pause"></i></a>
						</li>
						<?php endif; ?>
                    </ul>
                </div>
				<?php } ?>
            </div>
        </div>
    </section>