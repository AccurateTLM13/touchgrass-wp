<?php
/**
 * Touch Grass microcopy map: the two-register copy system.
 *
 * Pattern: product presentation speaks straight-faced luxury; the
 * transaction layer (cart, checkout, emails, errors) speaks mercenary.
 * Every string lives in one editable map so the voice stays consistent
 * and merchants can rewrite it without touching code.
 *
 * Storage: one option per key (tg_microcopy_{key}), registered with the
 * Settings API and rendered on the Touch Grass dashboard ("Microcopy"
 * section). tg_microcopy() falls back to the defaults below.
 *
 * Wiring: WooCommerce string filters pull from the map — checkout button,
 * thank-you page, empty cart, coupon label, stock notices, email headings.
 * The existing demo-mode GOOUTSIDE coupon hint is preserved and takes
 * precedence over the map when demo mode is on.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class TG_Microcopy {

	/**
	 * The map: key => default string. {n} is replaced with a live count
	 * where noted.
	 *
	 * @return array
	 */
	public static function defaults() {
		return [
			'add_to_cart'       => __( 'Claim Your Plot', 'touchgrass-core' ),
			'empty_cart'        => __( 'Nothing here. Like your step count.', 'touchgrass-core' ),
			'cart_guarantee'    => __( 'Your money is safe. For now.', 'touchgrass-core' ),
			'coupon_label'      => __( 'Bribe code', 'touchgrass-core' ),
			'checkout_button'   => __( 'Complete Invoice', 'touchgrass-core' ),
			'order_received_title' => __( 'Invoice paid. Grass dispatched.', 'touchgrass-core' ),
			'order_received_text'  => __( "We're not judging. We're invoicing.", 'touchgrass-core' ),
			'empty_search'      => __( "No grass found. Have you tried outside? It's free.", 'touchgrass-core' ),
			/* {n} = live stock count at the low-stock threshold. */
			'low_stock'         => __( 'Only {n} left. The grass is not infinite. Unlike your screen time.', 'touchgrass-core' ),
			'newsletter_consent' => __( "Rare (but invoiced) emails. Unsubscribe anytime; we'll pretend it never happened.", 'touchgrass-core' ),
			'newsletter_success' => __( "You're on the list. The grass will write.", 'touchgrass-core' ),
			'out_of_stock'      => __( 'Gone. The grass has left the building.', 'touchgrass-core' ),
		];
	}

	/**
	 * Field labels for the settings screen.
	 *
	 * @return array key => label
	 */
	public static function labels() {
		return [
			'add_to_cart'       => __( 'Add to cart button', 'touchgrass-core' ),
			'empty_cart'        => __( 'Empty cart message', 'touchgrass-core' ),
			'cart_guarantee'    => __( 'Cart guarantee line', 'touchgrass-core' ),
			'coupon_label'      => __( 'Coupon field label', 'touchgrass-core' ),
			'checkout_button'   => __( 'Checkout button', 'touchgrass-core' ),
			'order_received_title' => __( 'Order received heading', 'touchgrass-core' ),
			'order_received_text'  => __( 'Order received text', 'touchgrass-core' ),
			'empty_search'      => __( 'Empty search message', 'touchgrass-core' ),
			'low_stock'         => __( 'Low stock notice ({n} = count)', 'touchgrass-core' ),
			'newsletter_consent' => __( 'Newsletter consent line', 'touchgrass-core' ),
			'newsletter_success' => __( 'Newsletter success message', 'touchgrass-core' ),
			'out_of_stock'      => __( 'Out of stock text', 'touchgrass-core' ),
		];
	}

	public static function option_key( $key ) {
		return 'tg_microcopy_' . $key;
	}

	public static function init() {
		add_action( 'admin_init', [ __CLASS__, 'register' ] );
		add_action( 'init', [ __CLASS__, 'wire' ] );
	}

	public static function register() {
		foreach ( self::defaults() as $key => $default ) {
			register_setting( 'tg_settings', self::option_key( $key ), [
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => $default,
			] );
		}

		add_settings_section(
			'tg_microcopy_section',
			__( 'Microcopy', 'touchgrass-core' ),
			[ __CLASS__, 'section_text' ],
			'tg_settings'
		);

		$labels = self::labels();
		foreach ( self::defaults() as $key => $default ) {
			add_settings_field(
				self::option_key( $key ),
				$labels[ $key ],
				[ __CLASS__, 'field' ],
				'tg_settings',
				'tg_microcopy_section',
				[ 'key' => $key ]
			);
		}
	}

	public static function section_text() {
		echo '<p>' . esc_html__( 'The two-register copy system: product pages speak luxury, the transaction layer speaks mercenary. Every string below is used somewhere real — empty the field to fall back to the default shown.', 'touchgrass-core' ) . '</p>';
	}

	public static function field( $args ) {
		$key   = $args['key'];
		$value = get_option( self::option_key( $key ), self::defaults()[ $key ] );
		echo '<input type="text" name="' . esc_attr( self::option_key( $key ) ) . '" value="' . esc_attr( $value ) . '" class="large-text">';
	}

	/**
	 * WooCommerce + theme wiring. All behind the map; merchants edit text,
	 * never code.
	 */
	public static function wire() {
		if ( ! function_exists( 'tg_core_woo_active' ) || ! tg_core_woo_active() ) {
			return;
		}

		/* Checkout button. */
		add_filter( 'woocommerce_order_button_text', function () {
			return tg_microcopy( 'checkout_button' );
		} );

		/* Add-to-cart buttons (single product + archives). */
		add_filter( 'woocommerce_product_single_add_to_cart_text', function () {
			return tg_microcopy( 'add_to_cart' );
		} );
		add_filter( 'woocommerce_product_add_to_cart_text', function () {
			return tg_microcopy( 'add_to_cart' );
		} );

		/* Empty cart message: classic shortcode cart. */
		add_filter( 'wc_empty_cart_message', function () {
			return tg_microcopy( 'empty_cart' );
		} );

		/* Block-based cart: the empty message is saved block content. Swap only
		 * the default text so merchant-customized messages survive. */
		add_filter( 'render_block', function ( $content, $block ) {
			if ( ( $block['blockName'] ?? '' ) === 'woocommerce/empty-cart-block'
				&& false !== strpos( $content, 'Your cart is currently empty!' ) ) {
				$content = str_replace(
					'Your cart is currently empty!',
					esc_html( tg_microcopy( 'empty_cart' ) ),
					$content
				);
			}
			return $content;
		}, 10, 2 );

		/* Thank-you page banner (order-received heading + text). */
		add_action( 'woocommerce_thankyou', function ( $order_id ) {
			echo '<div class="tg-invoice-banner" role="status">'
				. '<strong>' . esc_html( tg_microcopy( 'order_received_title' ) ) . '</strong><br>'
				. esc_html( tg_microcopy( 'order_received_text' ) )
				. '</div>';
		}, 5 );

		/* Coupon field label. Demo-mode GOOUTSIDE hint keeps precedence. */
		add_filter( 'gettext', function ( $translated, $text, $domain ) {
			if ( 'woocommerce' === $domain && 'Coupon code' === $text ) {
				if ( function_exists( 'tg_demo_mode' ) && tg_demo_mode()
					&& function_exists( 'wc_get_coupon_id_by_code' )
					&& wc_get_coupon_id_by_code( 'GOOUTSIDE' ) ) {
					return __( 'Promo code (try GOOUTSIDE)', 'touchgrass-core' );
				}
				return tg_microcopy( 'coupon_label' );
			}
			return $translated;
		}, 10, 3 );

		/* Stock notices: low-stock uses the live count; out-of-stock is final. */
		add_filter( 'woocommerce_get_stock_html', function ( $html, $product ) {
			if ( ! $product instanceof WC_Product ) { return $html; }
			if ( ! $product->managing_stock() ) { return $html; }
			$qty = $product->get_stock_quantity();
			if ( $product->is_in_stock() && $qty <= $product->get_low_stock_amount() ) {
				$text = str_replace( '{n}', number_format_i18n( (int) $qty ), tg_microcopy( 'low_stock' ) );
				return '<p class="stock in-stock tg-low-stock">' . esc_html( $text ) . '</p>';
			}
			if ( ! $product->is_in_stock() ) {
				return '<p class="stock out-of-stock">' . esc_html( tg_microcopy( 'out_of_stock' ) ) . '</p>';
			}
			return $html;
		}, 10, 2 );

		/* Order email, styled as a property deed. */
		add_filter( 'woocommerce_email_heading_customer_processing_order', function () {
			return __( 'Deed of Grass Conveyance', 'touchgrass-core' );
		} );
	}

}

TG_Microcopy::init();

/**
 * Microcopy lookup with default fallback. Empty saved values fall back to
 * the default so a cleared field never renders blank.
 *
 * @param string $key Map key.
 * @return string
 */
function tg_microcopy( $key ) {
	$defaults = TG_Microcopy::defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	$value    = get_option( TG_Microcopy::option_key( $key ), $default );
	$value    = trim( (string) $value );
	return '' !== $value ? $value : $default;
}
