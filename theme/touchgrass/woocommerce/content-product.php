<?php
/**
 * Product card for loops — brand-styled.
 */
defined( 'ABSPATH' ) || exit;

global $product;
if ( empty( $product ) || ! $product->is_visible() ) { return; }
$badge = tg_product_badge( $product );
?>
<li <?php wc_product_class( 'pcard', $product ); ?>>
	<a class="pcard-link" href="<?php echo esc_url( $product->get_permalink() ); ?>" style="display:block;text-decoration:none;color:inherit">
		<div class="ph">
			<?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
			<?php if ( $product->is_on_sale() ) { echo '<span class="onsale" style="position:absolute">' . esc_html__( 'Sale', 'touchgrass' ) . '</span>'; } ?>
		</div>
		<div class="pb">
			<span class="tg-badge"><?php echo $badge ? esc_html( $badge ) : '&nbsp;'; ?></span>
			<?php echo '<h2 class="woocommerce-loop-product__title">' . esc_html( $product->get_name() ) . '</h2>'; ?>
			<div class="tg-tagline"><?php echo esc_html( tg_product_tagline( $product ) ); ?></div>
			<div class="price" style="display:flex;align-items:center;justify-content:space-between">
				<span><?php echo $product->get_price_html(); // phpcs:ignore ?></span>
				<?php echo tg_stars( 5 ); ?>
			</div>
		</div>
	</a>
	<?php woocommerce_template_loop_add_to_cart(); ?>
</li>
