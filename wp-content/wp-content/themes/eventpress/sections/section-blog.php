<?php 
	$eventpress_hs_blog 			= get_theme_mod('hide_show_blog','1');
	$eventpress_blog_title 	   	    = get_theme_mod('blog_title');
	$eventpress_blog_desc 			= get_theme_mod('blog_description');
	$eventpress_blog_display_num 	= get_theme_mod('blog_display_num','3');	
	 if($eventpress_hs_blog == '1') { 
?>
 <!--===================== 
        Start: Latest News
     =====================-->
    <section id="latest-news" class="section-padding">
        <div class="container">
			<?php if ( ! empty( $eventpress_blog_title ) || ! empty( $eventpress_blog_desc )) :?>
				<div class="row">
					<div class="col-md-6 offset-md-3 text-center">
						<div class="section-title">
							<?php if($eventpress_blog_title) {?>
									<h2><?php echo esc_html($eventpress_blog_title); ?></h2>
							<?php } ?>
							<?php 
								if ( function_exists( 'eventpress_title_seprator' ) ) :
									eventpress_title_seprator(); 
								endif;	
							?>
							<?php if($eventpress_blog_desc) {?>
								<p><?php echo esc_html($eventpress_blog_desc); ?></p>
							<?php } ?>
						</div>
					</div>
				</div>
			<?php endif; ?>	
            <div class="row">
				<?php 	
				if ( function_exists( 'evento_activate' ) ) { 
					$eventpress_args = array( 'post_type' => 'post', 'posts_per_page' => $eventpress_blog_display_num,'post__not_in'=>get_option("sticky_posts")) ; 	
				}else{
					$eventpress_args = array( 'post_type' => 'post','post__not_in'=>get_option("sticky_posts")) ; 	
				}
				 $eventpress_wp_query = new WP_Query($eventpress_args);
				if($eventpress_wp_query)
				{	
				while($eventpress_wp_query->have_posts()):$eventpress_wp_query->the_post(); ?>
					<div class="col-lg-4 col-sm-6 mb-4">
						<div class="single-news" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<div class="ln-img">
								<div class="post-overlay">
									<a href="<?php esc_url(get_permalink()); ?>"><i class="fa fa-link"></i></a>
								</div>
								<?php if ( has_post_thumbnail() ) { ?>
									<?php the_post_thumbnail(); ?>
								<?php } ?>							
								<div class="news-date">
									<p><?php echo esc_html(get_the_date('j M Y')); ?></p>
								</div>
							</div>
							<div class="ln-content">
								<?php     
									if ( is_single() ) :
									
									the_title('<h4 class="post-title">', '</h4>' );
									
									else:
									
									the_title( sprintf( '<h4 class="post-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h4>' );
									
									endif; 
								?> 
								<ul>
									<li><i class="fa fa-user"></i><?php esc_html(the_author()); ?></li>
									<?php   $eventpress_cat_list = get_the_category_list();
										if(!empty($eventpress_cat_list)) { ?>
											<li><i class="fa  fa-folder-open"></i><a href="<?php esc_url(get_permalink()); ?>"><?php the_category(', '); ?></a></li>
									<?php } ?>		
								</ul>
									<?php 
										the_content( 
											sprintf( 
												__( 'Read More', 'eventpress' ), 
												'<span class="screen-reader-text">  '.esc_html(get_the_title()).'</span>' 
											) 
										);
									?>
							</div>
						</div>
					</div>
				<?php 
					endwhile; 
					wp_reset_postdata(); 
					}
				?>
            </div>
        </div>
    </section>
    <!--===================== 
        End: Latest News
     =====================-->
	 <?php } ?> 