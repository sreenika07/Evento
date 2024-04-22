<?php
/**
 * The template for displaying archive pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Eventpress
 */

get_header();
?>
 <section id="blog-content" class="section-padding page-s">
        <div class="container">

            <div class="row">
                <!-- Blog Content -->
                <div class="<?php esc_attr(eventpress_post_layout()); ?> order-lg-1 mb-5 mb-lg-0">
					<?php if( have_posts() ): ?>
						<?php while( have_posts() ): the_post(); ?>
							<article class="blog-post single-blog">
								<div class="post-thumb">
									<?php if ( has_post_thumbnail() ) { ?>
										<?php the_post_thumbnail(); ?>
									<?php } ?>	
								</div>
								<ul class="meta-info">
									<li class="post-date"><a href="<?php echo esc_url(get_month_link(get_post_time('Y'),get_post_time('m'))); ?>"><?php echo esc_html(get_the_date('j M Y')); ?></a></li>
									<li class="posted-by"><i class="fa fa-user"></i> <a href="<?php echo esc_url(get_author_posts_url( get_the_author_meta( 'ID' ) ));?>"><?php esc_html(the_author()); ?> </a></li>
									<li class="comments-quantity"><a href="<?php esc_url(the_permalink()); ?>"> - <?php echo get_comments_number(); ?> <?php esc_html_e('Comments','eventpress'); ?></a></li>
									<li class="comments-quantity"><i class="fa  fa-folder-open"></i><a href="<?php esc_url(the_permalink()); ?>"> <?php the_category(', '); ?></a></li>
									<?php if ( has_tag() ) : ?>
										<li class="tags"><i class="fa fa-tags"></i><a href="<?php esc_url(the_permalink()); ?>"> <?php the_tags(' '); ?></a></li>
									<?php endif; ?>		
								</ul>
								<div class="post-content">
									<?php  
										if ( is_single() ) :
											the_title('<h4 class="post-title">', '</h4>' );
										else:
											the_title( sprintf( '<h4 class="post-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h4>' );
										endif; 
									?> 
									<?php 
										the_content( 
											sprintf( 
												__( 'Read More', 'eventpress' ), 
												'<span class="screen-reader-text">  '.esc_html(get_the_title()).'</span>' 
											) 
										);
									?>
								</div>
							</article>
							<?php endwhile; 
							endif; ?>
                    <?php comments_template( '', true ); // show comments  ?>
                </div>
                <?php get_sidebar(); ?>
            </div>

        </div>
    </section>

<?php get_footer(); ?>
