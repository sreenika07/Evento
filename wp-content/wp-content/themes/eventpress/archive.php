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

<section class="section-padding page-s">
        <div class="container">
            <div class="row">	
			
			<!--Blog Detail-->
			<div class="<?php esc_attr(eventpress_post_layout()); ?> order-lg-1 mb-5 mb-lg-0">
					
					<?php if( have_posts() ): ?>
						<div class="row">
							<?php while( have_posts() ): the_post(); ?>
							
								<?php get_template_part('template-parts/content','page'); ?>
						
							<?php endwhile; ?>
						</div>
						
					<?php else: ?>
						
						<?php get_template_part('template-parts/content','none'); ?>
						
					<?php endif; ?>
			
			</div>
			<!--/End of Blog Detail-->
			<?php get_sidebar(); ?>
		</div>	
	</div>
</section>

<?php get_footer(); ?>
