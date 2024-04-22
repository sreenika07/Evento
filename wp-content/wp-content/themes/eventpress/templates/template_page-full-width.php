<?php
/**
Template Name: Page Fullwidth
**/

get_header();
?>

<section class="site-content section-padding page-s">
	<div class="container">

		<div class="row">
			<!-- Blog Content -->
			<div class="col-lg-12 col-md-12 mb-5 mb-lg-0">
				<?php the_post(); the_content(); ?>
				<?php 
					if( $post->comment_status == 'open' ) { 
						comments_template( '', true ); // show comments 
					}
				?>	
			</div>
		</div>

	</div>
</section>

<?php get_footer(); ?>

