<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Eventpress
 */

get_header(); ?>
<section id="blog-content" class="section-padding page-s">
	<div class="container">
		<div class="row">
			<!-- Blog Content -->
			<div class="<?php esc_attr(eventpress_post_layout()); ?> order-lg-1 mb-5 mb-lg-0">
				<?php 
					$eventpress_paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
					$args = array( 'post_type' => 'post','paged'=>$eventpress_paged );	
					$loop = new WP_Query( $args );
				?>
					<?php if( $loop->have_posts() ): ?>
						<?php while( $loop->have_posts() ): $loop->the_post(); 
								get_template_part('template-parts/content','page'); 
						endwhile; ?>	
					<!-- Pagination -->
					<nav class="pagination mt-4" aria-label="Page navigation example">
						<?php			
						$GLOBALS['wp_query']->max_num_pages = $loop->max_num_pages;						
						// Previous/next page navigation.
						the_posts_pagination( array(
						'prev_text'          => '<i class="fa fa-angle-double-left"></i>',
						'next_text'          => '<i class="fa fa-angle-double-right"></i>',
						) ); ?>
					<!-- Pagination -->	
					</nav>
				<?php else: ?>
					<?php get_template_part('template-parts/content','none'); ?>
				<?php endif; ?>
			</div>
			<?php get_sidebar(); ?>
		</div>
	</div>
</section>
<?php get_footer(); ?>
