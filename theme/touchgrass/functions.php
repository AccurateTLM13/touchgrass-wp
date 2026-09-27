<?php
/**
 * Touch Grass theme setup.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/template-tags.php';

define( 'TG_VERSION', '2.0.0' );

/* WooCommerce is required for the shop; nudge the admin if it's missing. */
add_action( 'admin_notices', function () {
	if ( class_exists( 'WooCommerce' ) ) { return; }
	echo '<div class="notice notice-warning"><p>'
		. esc_html__( 'Touch Grass theme: WooCommerce is not active. The storefront design will load, but the shop, cart, and checkout need WooCommerce.', 'touchgrass' )
		. '</p></div>';
} );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'custom-logo' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
	register_nav_menus( [
		'primary' => __( 'Primary navigation', 'touchgrass' ),
		'foot_shop' => __( 'Footer — Shop', 'touchgrass' ),
		'foot_company' => __( 'Footer — Company', 'touchgrass' ),
		'foot_support' => __( 'Footer — Support', 'touchgrass' ),
	] );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'tg-fonts', 'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,400;1,9..144,500&family=Inter:wght@400;500;600;700&display=swap', [], null );
	wp_enqueue_style( 'touchgrass', get_template_directory_uri() . '/assets/css/main.css', [ 'tg-fonts' ], TG_VERSION );
	wp_enqueue_script( 'touchgrass', get_template_directory_uri() . '/assets/js/main.js', [], TG_VERSION, true );
} );

/* Cart count for the header button. */
add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	ob_start();
	tg_cart_button_inner();
	$fragments['.tg-cart-inner'] = ob_get_clean();
	return $fragments;
} );

function tg_cart_button_inner() {
	$count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
	echo '<span class="tg-cart-inner">';
	echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6h15l-1.5 9h-12z"/><circle cx="9" cy="20" r="1.6"/><circle cx="18" cy="20" r="1.6"/></svg>';
	echo '<span class="cart-label">' . esc_html__( 'Cart', 'touchgrass' ) . '</span>';
	echo '<span class="cart-count">' . esc_html( $count ) . '</span>';
	echo '</span>';
}

/* Star rating helper (gold stars, no icon fonts). */
function tg_stars( $rating = 5 ) {
	$full = str_repeat( '★', (int) $rating );
	return '<span class="stars" aria-label="' . esc_attr( $rating ) . ' out of 5 stars">' . $full . '</span>';
}

/* tg_product_badge() / tg_product_tagline() live in the core plugin.
   front-page.php guards both with function_exists(), so the theme
   degrades gracefully when the plugin is inactive. */

/* Keep the shop tidy: 9 products per page, our catalogue size. */
add_filter( 'loop_shop_per_page', function () { return 9; } );

/* Demo-store notice on cart + checkout. */
add_action( 'woocommerce_before_cart', 'tg_demo_notice' );
add_action( 'woocommerce_before_checkout_form', 'tg_demo_notice' );
function tg_demo_notice() {
	echo '<div class="tg-demo-note"><strong>' . esc_html__( 'Demo store.', 'touchgrass' ) . '</strong> '
		. esc_html__( 'Checkout is a mock — no card is charged, no grass is actually shipped. Yet.', 'touchgrass' ) . '</div>';
}

/* Rename the coupon label copy on cart. */
add_filter( 'gettext', function ( $translated, $text, $domain ) {
	if ( 'woocommerce' === $domain && 'Coupon code' === $text ) {
		return 'Promo code (try GOOUTSIDE)';
	}
	return $translated;
}, 10, 3 );

/* WooCommerce content wrappers (replaces woocommerce.php so template overrides work). */
add_action( 'woocommerce_before_main_content', function () {
	echo '<div class="wrap" style="padding-top:3rem">';
}, 5 );
add_action( 'woocommerce_after_main_content', function () {
	echo '</div>';
}, 50 );
