<?php
/**
 * Touch Grass theme setup.
 *
 * Requires: WordPress 6.5+, PHP 8.1+. WooCommerce is optional: without it,
 * normal pages and posts render and commerce UI hides itself.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/template-tags.php';
require_once get_template_directory() . '/inc/seo.php';

/*
 * Product thumbnails render below the fold everywhere they appear
 * (lineup grids, ritual cross-sells). Core's loading optimizer can
 * misclassify them as in-viewport and strip an explicit loading="lazy",
 * so enforce it here for woocommerce_thumbnail images only.
 */
add_filter( 'wp_get_loading_optimization_attributes', function ( $attrs, $tag_name, $attr ) {
	if ( 'img' === $tag_name && isset( $attr['class'] ) && str_contains( (string) $attr['class'], 'attachment-woocommerce_thumbnail' ) ) {
		$attrs['loading'] = 'lazy';
		unset( $attrs['fetchpriority'] );
	}
	return $attrs;
}, 10, 3 );

define( 'TG_VERSION', '2.6.0' );

/* WooCommerce is optional; nudge admins (not visitors) if it's missing. */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	if ( class_exists( 'WooCommerce' ) ) { return; }
	echo '<div class="notice notice-warning"><p>'
		. esc_html__( 'Touch Grass theme: WooCommerce is not active. The storefront design will load, but the shop, cart, and checkout need WooCommerce.', 'touchgrass' )
		. '</p></div>';
} );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'custom-logo', [
		'height'      => 48,
		'width'       => 220,
		'flex-height' => true,
		'flex-width'  => true,
	] );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
	register_nav_menus( [
		'primary'      => __( 'Primary navigation', 'touchgrass' ),
		'foot_shop'    => __( 'Footer — Shop', 'touchgrass' ),
		'foot_company' => __( 'Footer — Company', 'touchgrass' ),
		'foot_support' => __( 'Footer — Support', 'touchgrass' ),
	] );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'tg-fonts', 'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,400;1,9..144,500&family=Inter:wght@400;500;600;700&display=swap', [], null );
	wp_enqueue_style( 'touchgrass', get_template_directory_uri() . '/assets/css/main.css', [ 'tg-fonts' ], TG_VERSION );
	wp_enqueue_script( 'touchgrass', get_template_directory_uri() . '/assets/js/main.js', [], TG_VERSION, true );
} );

/* Cart count for the header button (WooCommerce only). */
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

/**
 * Star rating helper (gold stars, no icon fonts).
 *
 * @param float $rating Average rating, e.g. 4.5.
 * @param int   $count  Review count (0 hides the count suffix).
 * @return string
 */
function tg_stars( $rating = 5, $count = 0 ) {
	$rating = max( 0, min( 5, (float) $rating ) );
	$full   = (int) floor( $rating );
	$half   = ( $rating - $full ) >= 0.5 ? 1 : 0;
	$stars  = str_repeat( '★', $full ) . ( $half ? '⯪' : '' );
	$label  = sprintf(
		/* translators: 1: rating, 2: review count */
		__( '%1$s out of 5 stars', 'touchgrass' ),
		number_format_i18n( $rating, 1 )
	);
	if ( $count > 0 ) {
		/* translators: 1: review count */
		$label .= ' ' . sprintf( __( '(%d reviews)', 'touchgrass' ), $count );
	}
	return '<span class="stars" aria-label="' . esc_attr( $label ) . '">' . esc_html( $stars ) . '</span>';
}

/* tg_product_badge() / tg_product_tagline() live in the core plugin.
   Every theme call site guards them with function_exists(), so the theme
   degrades gracefully when the plugin is inactive. */

/* Products per shop page: editable in the Customizer (default 9). */
add_filter( 'loop_shop_per_page', function () {
	return max( 1, (int) tg_brand( 'tg_products_per_page' ) ?: 9 );
} );

/* Demo-store notice on cart + checkout — demo mode only. Covers both the
 * classic shortcode templates (woocommerce_before_cart / _checkout_form) and
 * the Cart/Checkout blocks (render_block filter on the top-level blocks). */
add_action( 'woocommerce_before_cart', 'tg_demo_notice' );
add_action( 'woocommerce_cart_is_empty', 'tg_demo_notice' );
add_action( 'woocommerce_before_checkout_form', 'tg_demo_notice' );
function tg_demo_notice() {
	echo tg_demo_notice_html(); // phpcs:ignore
}
function tg_demo_notice_html() {
	if ( ! function_exists( 'tg_demo_mode' ) || ! tg_demo_mode() ) { return ''; }
	return '<div class="tg-demo-note" role="note"><strong>' . esc_html__( 'Demo store.', 'touchgrass' ) . '</strong> '
		. esc_html__( 'Checkout is a mock — no card is charged, no grass is actually shipped. Yet.', 'touchgrass' ) . '</div>';
}
add_filter( 'render_block', function ( $block_content, $block ) {
	if ( ! isset( $block['blockName'] ) || ! in_array( $block['blockName'], [ 'woocommerce/cart', 'woocommerce/checkout' ], true ) ) {
		return $block_content;
	}
	return tg_demo_notice_html() . $block_content;
}, 10, 2 );

/* Coupon field label + demo hint live in the core plugin (TG_Microcopy::wire):
 * the plugin checks demo mode for the GOOUTSIDE hint and otherwise pulls the
 * merchant-editable "Abatement code" label from the microcopy map. */

/* PDP spine (P4): the tabs/upsells/related callbacks normally printed by
 * woocommerce_after_single_product_summary are replaced by the custom
 * content-single-product.php flow — "Complete the Ritual" cross-sells,
 * accordions, reviews, trust block. */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_related_products', 20 );

/* PDP accordions: Shipping & Returns and How It Ships join the product
 * tabs API (Description is core, The Deed comes from the plugin). The
 * reviews tab is unset here — reviews render as their own section after
 * the accordions. */
function tg_product_tab_shipping() {
	echo '<p>' . esc_html__( 'Orders leave the greenhouse within 48 hours. Shipping is calculated at checkout like a responsible adult.', 'touchgrass' ) . '</p>';
	echo '<p>' . esc_html__( 'Returns: 30 days, no interrogation. If your grass dies within 30 days, we replace it.', 'touchgrass' ) . '</p>';
}
function tg_product_tab_how_it_ships() {
	echo '<p>' . esc_html__( 'Your grass arrives in the Deed Box: a deed-styled box with a tiny brass plaque bearing your plot’s name. The plaque is real brass. The deed is legally meaningless.', 'touchgrass' ) . '</p>';
}
add_filter( 'woocommerce_product_tabs', function ( $tabs ) {
	unset( $tabs['reviews'] );
	$tabs['tg_shipping'] = [
		'title'    => __( 'Shipping & Returns', 'touchgrass' ),
		'priority' => 30,
		'callback' => 'tg_product_tab_shipping',
	];
	$tabs['tg_how_it_ships'] = [
		'title'    => __( 'How It Ships', 'touchgrass' ),
		'priority' => 35,
		'callback' => 'tg_product_tab_how_it_ships',
	];
	return $tabs;
} );

/* Trust block (compact) after the cart. Uses the_content so it renders for
 * both the classic shortcode cart and the block cart. */
add_filter( 'the_content', function ( $content ) {
	if ( function_exists( 'is_cart' ) && is_cart()
		&& is_main_query() && in_the_loop()
		&& function_exists( 'tg_trust_block' ) ) {
		ob_start();
		tg_trust_block( 'compact' );
		$content .= ob_get_clean();
	}
	return $content;
} );

/* WooCommerce content wrappers (replaces woocommerce.php so template overrides work). */
add_action( 'woocommerce_before_main_content', function () {
	echo '<div class="wrap" style="padding-top:3rem">';
}, 5 );
add_action( 'woocommerce_after_main_content', function () {
	echo '</div>';
}, 50 );
