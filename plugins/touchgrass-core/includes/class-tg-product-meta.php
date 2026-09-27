<?php
/**
 * Touch Grass product fields: _tg_tagline and _tg_badge.
 * Registered here (functionality); read by the theme (presentation).
 * Contract: ~/workspace/repos/touchgrass-wp/CONTRACT.md
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class TG_Product_Meta {

	public static function init() {
		add_action( 'init', [ __CLASS__, 'register' ] );
		add_action( 'add_meta_boxes', [ __CLASS__, 'meta_box' ] );
		add_action( 'save_post_product', [ __CLASS__, 'save' ] );
	}

	public static function register() {
		register_post_meta( 'product', '_tg_tagline', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'auth_callback' => function () { return current_user_can( 'edit_products' ); },
		] );
		register_post_meta( 'product', '_tg_badge', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'auth_callback' => function () { return current_user_can( 'edit_products' ); },
		] );
	}

	public static function badge_options() {
		return [
			''           => __( '— none —', 'touchgrass-core' ),
			'bestseller' => __( 'Bestseller', 'touchgrass-core' ),
			'lowstock'   => __( 'Low stock', 'touchgrass-core' ),
			'staffpick'  => __( 'Staff pick', 'touchgrass-core' ),
		];
	}

	public static function meta_box() {
		if ( ! tg_core_woo_active() ) { return; }
		add_meta_box(
			'tg_product_fields',
			__( 'Touch Grass', 'touchgrass-core' ),
			[ __CLASS__, 'render' ],
			'product',
			'side',
			'default'
		);
	}

	public static function render( $post ) {
		wp_nonce_field( 'tg_product_meta', 'tg_product_meta_nonce' );
		$tagline = get_post_meta( $post->ID, '_tg_tagline', true );
		$badge   = get_post_meta( $post->ID, '_tg_badge', true );
		echo '<label for="tg_tagline_field"><strong>' . esc_html__( 'Tagline', 'touchgrass-core' ) . '</strong></label><br>';
		echo '<input type="text" id="tg_tagline_field" name="tg_tagline" value="' . esc_attr( $tagline ) . '" style="width:100%;margin:6px 0 12px" maxlength="120"><br>';
		echo '<label for="tg_badge_field"><strong>' . esc_html__( 'Badge', 'touchgrass-core' ) . '</strong></label><br>';
		echo '<select id="tg_badge_field" name="tg_badge" style="width:100%;margin-top:6px">';
		foreach ( self::badge_options() as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '"' . selected( $badge, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';
	}

	public static function save( $post_id ) {
		if ( ! isset( $_POST['tg_product_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['tg_product_meta_nonce'] ), 'tg_product_meta' ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
		if ( isset( $_POST['tg_tagline'] ) ) {
			update_post_meta( $post_id, '_tg_tagline', sanitize_text_field( wp_unslash( $_POST['tg_tagline'] ) ) );
		}
		if ( isset( $_POST['tg_badge'] ) ) {
			$badge = sanitize_key( $_POST['tg_badge'] );
			$allowed = [ '', 'bestseller', 'lowstock', 'staffpick' ];
			update_post_meta( $post_id, '_tg_badge', in_array( $badge, $allowed, true ) ? $badge : '' );
		}
	}
}

TG_Product_Meta::init();

/**
 * Display label for a product's _tg_badge value.
 *
 * @param WC_Product|int $product Product object or ID.
 * @return string Badge label, or '' when none.
 */
function tg_product_badge( $product ) {
	$id    = $product instanceof WC_Product ? $product->get_id() : (int) $product;
	$badge = get_post_meta( $id, '_tg_badge', true );
	$labels = [
		'bestseller' => __( 'Bestseller', 'touchgrass-core' ),
		'lowstock'   => __( 'Low stock', 'touchgrass-core' ),
		'staffpick'  => __( 'Staff pick', 'touchgrass-core' ),
	];
	return isset( $labels[ $badge ] ) ? $labels[ $badge ] : '';
}

/**
 * Tagline for a product (_tg_tagline).
 *
 * @param WC_Product|int $product Product object or ID.
 * @return string
 */
function tg_product_tagline( $product ) {
	$id = $product instanceof WC_Product ? $product->get_id() : (int) $product;
	return get_post_meta( $id, '_tg_tagline', true );
}
