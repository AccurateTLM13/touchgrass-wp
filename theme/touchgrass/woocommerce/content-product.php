<?php
/**
 * Product card for loops — brand-styled.
 *
 * Keeps WooCommerce's required loop structure: <li> inside the <ul.products>
 * that woocommerce_product_loop_start() opens, wc_product_class(), the
 * permalink, and woocommerce_template_loop_add_to_cart() so AJAX add-to-cart
 * and third-party extensions keep working.
 */
defined( 'ABSPATH' ) || exit;

global $product;
if ( empty( $product ) || ! $product->is_visible() ) { return; }
/* The core plugin may be inactive; degrade to no badge/tagline, never fatal. */
$badge   = function_exists( 'tg_product_badge' ) ? tg_product_badge( $product ) : '';
$tagline = function_exists( 'tg_product_tagline' ) ? tg_product_tagline( $product ) : '';
?>
<li <?php wc_product_class( 'pcard', $product ); ?>>
	<a class="pcard-link" href="<?php echo esc_url( $product->get_permalink() ); ?>" style="display:block;text-decoration:none;color:inherit">
		<div class="ph">
			<?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
			<?php if ( $product->is_on_sale() ) { echo '<span class="onsale">' . esc_html__( 'Sale', 'touchgrass' ) . '</span>'; } ?>
		</div>
		<div class="pb">
			<span class="tg-badge"><?php echo $badge ? esc_html( $badge ) : '&nbsp;'; ?></span>
			<?php echo '<h2 class="woocommerce-loop-product__title">' . esc_html( $product->get_name() ) . '</h2>'; ?>
			<?php if ( $tagline ) : ?>
			<div class="tg-tagline"><?php echo esc_html( $tagline ); ?></div>
			<?php endif; ?>
			<div class="price">
				<span><?php echo $product->get_price_html(); // phpcs:ignore ?></span>
				<?php echo tg_product_rating_html( $product ); // phpcs:ignore ?>
			</div>
		</div>
	</a>
	<?php woocommerce_template_loop_add_to_cart(); ?>
</li>
