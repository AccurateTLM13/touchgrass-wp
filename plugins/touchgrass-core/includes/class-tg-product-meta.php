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
		add_filter( 'woocommerce_product_tabs', [ __CLASS__, 'deed_tab' ] );
	}

	/**
	 * "The Deed" product tab: deadpan spec sheet. Empty fields fall back to
	 * the house defaults, so the tab is never blank.
	 */
	public static function deed_tab( $tabs ) {
		if ( ! function_exists( 'tg_core_woo_active' ) || ! tg_core_woo_active() ) {
			return $tabs;
		}
		$tabs['tg_deed'] = [
			'title'    => __( 'The Deed', 'touchgrass-core' ),
			'priority' => 25,
			'callback' => [ __CLASS__, 'deed_tab_content' ],
		];
		return $tabs;
	}

	public static function deed_tab_content() {
		global $product;
		$data = tg_deed_data( $product );
		echo '<div class="tg-deed">';
		echo '<p class="tg-deed-lede">' . esc_html__( 'Every plot ships with its deed: provenance, specifications, and warranty, recorded for posterity. The deed is legally meaningless.', 'touchgrass-core' ) . '</p>';
		echo '<dl>';
		foreach ( $data as $row ) {
			echo '<div class="tg-deed-row"><dt>' . esc_html( $row[0] ) . '</dt><dd>' . esc_html( $row[1] ) . '</dd></div>';
		}
		echo '</dl></div>';
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
		foreach ( array_keys( self::deed_defaults() ) as $field ) {
			register_post_meta( 'product', '_tg_deed_' . $field, [
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'auth_callback' => function () { return current_user_can( 'edit_products' ); },
			] );
		}
		register_post_meta( 'product', '_tg_batch', [
			'show_in_rest'  => true,
			'single'        => true,
			'type'          => 'string',
			'auth_callback' => function () { return current_user_can( 'edit_products' ); },
		] );
	}

	/**
	 * "The Deed" spec defaults. Dry data is the joke infrastructure.
	 *
	 * @return array field => [ label, default ]
	 */
	public static function deed_defaults() {
		return [
			'provenance' => [ __( 'Provenance', 'touchgrass-core' ), __( 'Plot 7, Surrey Grassworks', 'touchgrass-core' ) ],
			'blades'     => [ __( 'Blade count', 'touchgrass-core' ), __( '~40,000', 'touchgrass-core' ) ],
			'sunlight'   => [ __( 'Sunlight requirement', 'touchgrass-core' ), __( 'Optional. Like your ambition.', 'touchgrass-core' ) ],
			'watering'   => [ __( 'Watering', 'touchgrass-core' ), __( 'Rain. Or tears.', 'touchgrass-core' ) ],
			'warranty'   => [ __( 'Warranty', 'touchgrass-core' ), __( 'Photosynthesis guaranteed for 30 days.', 'touchgrass-core' ) ],
		];
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
		echo '<select id="tg_badge_field" name="tg_badge" style="width:100%;margin:6px 0 12px">';
		foreach ( self::badge_options() as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '"' . selected( $badge, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select><br>';
		echo '<label for="tg_batch_field"><strong>' . esc_html__( 'Harvest batch', 'touchgrass-core' ) . '</strong></label><br>';
		echo '<input type="text" id="tg_batch_field" name="tg_batch" value="' . esc_attr( get_post_meta( $post->ID, '_tg_batch', true ) ) . '" style="width:100%;margin:6px 0 12px" maxlength="60" placeholder="' . esc_attr__( 'Batch No. 7', 'touchgrass-core' ) . '"><br>';
		echo '<strong>' . esc_html__( 'The Deed — spec sheet', 'touchgrass-core' ) . '</strong>';
		echo '<p class="description">' . esc_html__( 'Empty fields fall back to the house defaults.', 'touchgrass-core' ) . '</p>';
		foreach ( self::deed_defaults() as $field => $def ) {
			$value = get_post_meta( $post->ID, '_tg_deed_' . $field, true );
			echo '<label for="tg_deed_' . esc_attr( $field ) . '_field">' . esc_html( $def[0] ) . '</label><br>';
			echo '<input type="text" id="tg_deed_' . esc_attr( $field ) . '_field" name="tg_deed_' . esc_attr( $field ) . '" value="' . esc_attr( $value ) . '" style="width:100%;margin:6px 0 8px" maxlength="160" placeholder="' . esc_attr( $def[1] ) . '"><br>';
		}
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
		if ( isset( $_POST['tg_batch'] ) ) {
			update_post_meta( $post_id, '_tg_batch', sanitize_text_field( wp_unslash( $_POST['tg_batch'] ) ) );
		}
		foreach ( array_keys( self::deed_defaults() ) as $field ) {
			$input = 'tg_deed_' . $field;
			if ( isset( $_POST[ $input ] ) ) {
				update_post_meta( $post_id, '_tg_deed_' . $field, sanitize_text_field( wp_unslash( $_POST[ $input ] ) ) );
			}
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

/**
 * "The Deed" spec rows for a product: [label, value][]. Saved meta wins;
 * empty fields fall back to the house defaults.
 *
 * @param WC_Product|int $product Product object or ID.
 * @return array
 */
function tg_deed_data( $product ) {
	$id   = $product instanceof WC_Product ? $product->get_id() : (int) $product;
	$rows = [];
	foreach ( TG_Product_Meta::deed_defaults() as $field => $def ) {
		$value = trim( (string) get_post_meta( $id, '_tg_deed_' . $field, true ) );
		$rows[] = [ $def[0], '' !== $value ? $value : $def[1] ];
	}
	return $rows;
}

/**
 * Harvest batch label for a product (_tg_batch), e.g. "Batch No. 7".
 *
 * @param WC_Product|int $product Product object or ID.
 * @return string
 */
function tg_product_batch( $product ) {
	$id = $product instanceof WC_Product ? $product->get_id() : (int) $product;
	return trim( (string) get_post_meta( $id, '_tg_batch', true ) );
}

/**
 * Approximate blade count parsed from the deed's blade-count string.
 *
 * @param WC_Product|int $product Product object or ID.
 * @return int
 */
function tg_product_blades( $product ) {
	$id    = $product instanceof WC_Product ? $product->get_id() : (int) $product;
	$raw   = trim( (string) get_post_meta( $id, '_tg_deed_blades', true ) );
	if ( '' === $raw ) {
		$raw = TG_Product_Meta::deed_defaults()['blades'][1];
	}
	$digits = (int) preg_replace( '/[^0-9]/', '', $raw );
	return $digits > 0 ? $digits : 40000;
}
