<?php
/**
 * Single product: WooCommerce's default content-single-product template
 * (all hooks, notices, gallery, and extension compatibility retained),
 * followed by the brand guarantees block. Guarantees come from the same
 * Customizer fields as the homepage trust row — one source of truth.
 */
defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>
<div class="wrap" style="padding-top:1rem">
	<?php
	while ( have_posts() ) :
		the_post();
		wc_get_template_part( 'content', 'single-product' );
	endwhile;
	?>
	<div class="tg-guarantees" style="max-width:560px;margin:0 0 4rem">
		<?php $icons = [ 1 => '🚚', 2 => '🌱', 3 => '💧' ]; ?>
		<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
		<div><?php echo esc_html( $icons[ $i ] ); ?> <b><?php echo esc_html( tg_brand( "tg_guarantee_{$i}_title" ) ); ?>.</b> <?php echo esc_html( tg_brand( "tg_guarantee_{$i}_text" ) ); ?></div>
		<?php endfor; ?>
	</div>
</div>
<?php
get_footer( 'shop' );
