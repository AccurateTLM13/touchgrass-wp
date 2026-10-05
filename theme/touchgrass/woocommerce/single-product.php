<?php
/**
 * Single product wrapper: the PDP spine lives in
 * woocommerce/content-single-product.php (badge → title → tagline →
 * rating → price → ATC → sticky ATC → Complete the Ritual → accordions
 * → reviews → trust block).
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
</div>
<?php
get_footer( 'shop' );
