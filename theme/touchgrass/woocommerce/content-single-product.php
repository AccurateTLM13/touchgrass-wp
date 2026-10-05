<?php
/**
 * Single product content: the PDP spine.
 *
 * Order: gallery | badge → H1 → batch → tagline pitch → rating →
 * price + per-blade → excerpt → add-to-cart → sticky ATC (mobile) →
 * "Complete the Ritual" cross-sells → accordions (Description / The Deed /
 * Shipping & Returns / How It Ships) → reviews → trust block.
 *
 * The data tabs, upsells, and related products normally printed by
 * woocommerce_after_single_product_summary are unhooked in functions.php;
 * tabs are re-rendered as accordions via tg_product_accordions() and
 * reviews via comments_template().
 */
defined( 'ABSPATH' ) || exit;

global $product;

do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

	<?php do_action( 'woocommerce_before_single_product_summary' ); ?>

	<div class="summary entry-summary">
		<?php
		/* Badge — premium signal first. */
		$tg_badge = function_exists( 'tg_product_badge' ) ? tg_product_badge( $product ) : '';
		if ( $tg_badge ) :
			?>
			<p class="tg-pdp-badge"><?php echo esc_html( $tg_badge ); ?></p>
		<?php endif; ?>

		<?php woocommerce_template_single_title(); ?>

		<?php
		/* Harvest batch — provenance theater, printed on the deed. */
		$tg_batch = function_exists( 'tg_product_batch' ) ? tg_product_batch( $product ) : '';
		if ( $tg_batch ) :
			?>
			<p class="tg-pdp-batch"><?php
				/* translators: %s: batch label, e.g. "Batch No. 7" */
				printf( esc_html__( 'Harvest %s — cut this morning, invoiced this afternoon.', 'touchgrass' ), esc_html( $tg_batch ) );
			?></p>
		<?php endif; ?>

		<?php
		/* One-line pitch. */
		$tg_tagline = function_exists( 'tg_product_tagline' ) ? tg_product_tagline( $product ) : '';
		if ( $tg_tagline ) :
			?>
			<p class="tg-pdp-tagline"><?php echo esc_html( $tg_tagline ); ?></p>
		<?php endif; ?>

		<?php woocommerce_template_single_rating(); ?>

		<?php
		woocommerce_template_single_price();
		$tg_per_blade = tg_per_blade_line( $product );
		if ( $tg_per_blade ) :
			?>
			<p class="tg-per-blade"><?php echo esc_html( $tg_per_blade ); ?></p>
		<?php endif; ?>

		<?php
		/* Short description — suppressed when it duplicates the tagline
		 * one-line pitch above it. */
		$tg_excerpt = $product->get_short_description();
		if ( $tg_excerpt && trim( wp_strip_all_tags( $tg_excerpt ) ) !== trim( $tg_tagline ) ) {
			woocommerce_template_single_excerpt();
		}
		?>

		<?php woocommerce_template_single_add_to_cart(); ?>

		<?php woocommerce_template_single_meta(); ?>
	</div>

	<?php
	/* Sticky add-to-cart (mobile only, JS-gated): appears after scrolling
	 * past the main form. The button triggers the real form's button, so
	 * validation and extensions keep working. */
	$tg_atc_label = function_exists( 'tg_microcopy' )
		? tg_microcopy( 'add_to_cart' )
		: __( 'Add to cart', 'touchgrass' );
	?>
	<div class="tg-sticky-atc" aria-hidden="true">
		<div class="tg-sticky-atc-inner">
			<span class="tg-sticky-atc-thumb"><?php echo $product->get_image( 'thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="tg-sticky-atc-info">
				<strong><?php the_title(); ?></strong>
				<span class="tg-sticky-atc-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			</span>
			<button type="button" class="tg-sticky-atc-btn"><?php echo esc_html( $tg_atc_label ); ?></button>
		</div>
	</div>

	<?php tg_render_ritual_cross_sells(); ?>

	<?php tg_product_accordions(); ?>

	<?php comments_template(); ?>

	<?php
	if ( function_exists( 'tg_trust_block' ) ) {
		tg_trust_block( 'compact' );
	}
	?>

</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>
