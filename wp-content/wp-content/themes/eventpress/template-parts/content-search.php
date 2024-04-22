<?php
/**
 * Template part for displaying results in search pages.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package eventpress
 */

?>
<article id="post-<?php the_ID(); ?>" <?php post_class('blog-post'); ?>>
	<div class="post-thumb">
		<?php if ( has_post_thumbnail() ) { ?>
			<?php the_post_thumbnail(); ?>
		<?php } ?>	
		<div class="post-overlay">
			<a href="<?php esc_url(the_permalink()); ?>"><i class="fa fa-link"></i></a>
		</div>
	</div>
	<ul class="meta-info">
		<li class="post-date"><a href="<?php echo esc_url(get_month_link(get_post_time('Y'),get_post_time('m'))); ?>"><?php echo esc_html(get_the_date('j M Y')); ?></a></li>
		<li class="posted-by"><?php esc_html_e('By','eventpress'); ?> <a href="<?php echo esc_url(get_author_posts_url( get_the_author_meta( 'ID' ) ));?>"><?php esc_html(the_author()); ?></a></li>
		<li class="comments-quantity"><a href="<?php esc_url(the_permalink()); ?>"> <?php the_category(', '); ?></a></li>
	</ul>
	<div class="post-content">
		<h4 class="post-title"><a href="<?php esc_url(the_permalink()); ?>">
			<?php     
			if ( is_single() ) :
			
			the_title('<h4 class="post-title">', '</h5>' );
			
			else:
			
			the_title( sprintf( '<h4 class="post-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
			
			endif; 
		?>
		</a></h4>
		<p class="content">
			<?php 
				the_content( 
					sprintf( 
						__( 'Read More', 'eventpress' ), 
						'<span class="screen-reader-text">  '.esc_html(get_the_title()).'</span>' 
					) 
				);
			?>
		</p>
	</div>
</article>