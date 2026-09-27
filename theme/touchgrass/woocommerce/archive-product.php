<?php
/**
 * Custom shop archive: category filter pills + product grid in brand voice.
 */
defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$current_cat = is_product_category() ? get_queried_object()->slug : '';
$cats = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => true ] );
?>
<div class="wrap" style="padding-top:3rem">
	<header class="woocommerce-products-header">
		<div class="eyebrow"><?php _e( 'The collection', 'touchgrass' ); ?></div>
		<h1 class="woocommerce-products-header__title page-title" style="font-family:var(--serif);font-weight:500;font-size:52px;letter-spacing:-.02em;margin-bottom:.8rem"><?php woocommerce_page_title(); ?></h1>
		<?php do_action( 'woocommerce_archive_description' ); ?>
	</header>

	<div class="tg-shop-toolbar">
		<div class="tg-filters">
			<a class="tg-filter<?php echo $current_cat ? '' : ' on'; ?>" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"><?php _e( 'Everything', 'touchgrass' ); ?></a>
			<?php foreach ( $cats as $cat ) : ?>
			<a class="tg-filter<?php echo $current_cat === $cat->slug ? ' on' : ''; ?>" href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
			<?php endforeach; ?>
		</div>
		<?php do_action( 'woocommerce_before_shop_loop' ); ?>
	</div>

	<?php
	if ( woocommerce_product_loop() ) {
		woocommerce_product_loop_start();
		while ( have_posts() ) {
			the_post();
			wc_get_template_part( 'content', 'product' );
		}
		woocommerce_product_loop_end();
		do_action( 'woocommerce_after_shop_loop' );
	} else {
		do_action( 'woocommerce_no_products_found' );
	}
	?>
</div>
<?php
get_footer( 'shop' );
