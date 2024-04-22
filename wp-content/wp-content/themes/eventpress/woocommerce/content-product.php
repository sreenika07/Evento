<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>

 <div class="col-lg-6 col-md-6 col-sm-6 mb-4">
	<div class="gift-item">
		<?php if ( $product->is_on_sale() ) : ?>
			<?php echo apply_filters( 'woocommerce_sale_flash', '<span class="sale">' . esc_html__( 'Sale', 'eventpress' ) . '</span>', $post, $product ); ?>
		<?php endif; ?>
		<div class="gift-image">
			<?php the_post_thumbnail(); ?>
		</div>

		<div class="gift-details">
			<h4 class="woocommerce-loop-product__title"><?php the_title(); ?></h4>
			<div class="price">
				<?php echo $product->get_price_html(); ?>
			</div>
			<?php woocommerce_template_loop_add_to_cart(); ?>
		</div>
	</div>
</div>