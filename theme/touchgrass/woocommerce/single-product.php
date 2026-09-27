<?php
/**
 * Single product with brand guarantees block after the summary.
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
		<div>🚚 <b><?php _e( 'Free shipping over $50.', 'touchgrass' ); ?></b> <?php _e( 'The grass travels better than you do.', 'touchgrass' ); ?></div>
		<div>🌱 <b><?php _e( '30-day regrow guarantee.', 'touchgrass' ); ?></b> <?php _e( 'Dead grass is replaced free. No interrogation.', 'touchgrass' ); ?></div>
		<div>💧 <b><?php _e( 'Mist every 2–3 days.', 'touchgrass' ); ?></b> <?php _e( 'The Mister exists for exactly this ritual.', 'touchgrass' ); ?></div>
	</div>
</div>
<?php
get_footer( 'shop' );
