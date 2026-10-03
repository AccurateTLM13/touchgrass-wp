<?php
/**
 * Demo Pay: mock payment gateway for the Touch Grass store.
 *
 * This gateway exists ONLY for demo mode. It is registered with WooCommerce
 * only when tg_demo_mode() is true (off by default), and its own enabled
 * toggle defaults to "no". A production store never sees it.
 *
 * - Classic checkout: WC_Payment_Gateway subclass, id "touchgrass_demo".
 * - Cart/Checkout Blocks: server-side integration below + vanilla-JS
 *   registration in assets/js/demo-pay-blocks.js (no build step).
 *
 * Honest behavior: completing checkout marks the order "processing"
 * (WooCommerce's status for a paid order awaiting fulfillment). No card is
 * charged and nothing is shipped — it is a demo.
 *
 * The class declaration is guarded: the legacy standalone Demo Pay plugin
 * declared the same class name, and an old installation may still have that
 * plugin's files on disk. (The core plugin also suppresses the legacy plugin
 * from loading; this guard is the second layer.)
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* When demo mode is switched off, force the gateway's own toggle off too,
 * so re-enabling demo mode later starts from a known-safe state. */
add_action( 'update_option_tg_demo_mode', function ( $old_value, $new_value ) {
	if ( $old_value && ! $new_value ) {
		$settings = get_option( 'woocommerce_touchgrass_demo_settings', [] );
		$settings['enabled'] = 'no';
		update_option( 'woocommerce_touchgrass_demo_settings', $settings );
	}
}, 10, 2 );

/* Register the gateway only in demo mode. */
add_filter( 'woocommerce_payment_gateways', function ( $gateways ) {
	if ( ! tg_demo_mode() ) { return $gateways; }
	$gateways[] = 'TG_Demo_Pay_Gateway';
	return $gateways;
} );

add_action( 'plugins_loaded', function () {
	if ( ! tg_demo_mode() ) { return; }
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }
	if ( class_exists( 'TG_Demo_Pay_Gateway', false ) ) { return; }

	class TG_Demo_Pay_Gateway extends WC_Payment_Gateway {

		public function __construct() {
			$this->id                 = 'touchgrass_demo';
			$this->icon               = '';
			$this->has_fields         = false;
			$this->method_title       = __( 'Demo Pay', 'touchgrass-core' );
			$this->method_description = __( 'Mock checkout for demo mode. No real payment is processed; orders land in “processing”. Only available while demo mode is enabled.', 'touchgrass-core' );
			$this->title              = __( 'Demo Pay (demo mode)', 'touchgrass-core' );
			$this->description        = __( 'Demo mode: clicking “Place order” completes checkout instantly. No card, no charge, no grass shipped. Yet.', 'touchgrass-core' );
			$this->init_form_fields();
			$this->init_settings();
			$this->enabled = $this->get_option( 'enabled', 'no' );
			$this->title   = $this->get_option( 'title', $this->title );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		}

		public function init_form_fields() {
			$this->form_fields = [
				'enabled' => [
					'title'   => __( 'Enable/Disable', 'woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Demo Pay (requires demo mode)', 'touchgrass-core' ),
					'default' => 'no',
				],
				'title' => [
					'title'       => __( 'Title', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Name shown at checkout.', 'woocommerce' ),
					'default'     => __( 'Demo Pay (demo mode)', 'touchgrass-core' ),
				],
			];
		}

		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				wc_add_notice( __( 'Demo Pay could not find your order.', 'touchgrass-core' ), 'error' );
				return [ 'result' => 'failure' ];
			}
			/* Mock payment: mark paid → WooCommerce sets status to processing. */
			$order->payment_complete();
			$order->add_order_note( __( 'Demo Pay: mock payment accepted. No real charge made; order left in processing on purpose.', 'touchgrass-core' ) );
			if ( function_exists( 'WC' ) && WC()->cart ) {
				WC()->cart->empty_cart();
			}
			return [
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			];
		}
	}
} );

/* ---------- Cart/Checkout Blocks integration (demo mode only) ---------- */

add_action( 'woocommerce_blocks_loaded', function () {
	if ( ! tg_demo_mode() ) { return; }
	if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) { return; }
	if ( class_exists( 'TG_Demo_Pay_Blocks', false ) ) { return; }

	class TG_Demo_Pay_Blocks extends Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {

		protected $name = 'touchgrass_demo';

		public function initialize() {
			$this->settings = get_option( 'woocommerce_touchgrass_demo_settings', [] );
		}

		public function is_active() {
			if ( ! tg_demo_mode() ) { return false; }
			return filter_var( $this->get_setting( 'enabled', 'no' ), FILTER_VALIDATE_BOOLEAN );
		}

		public function get_payment_method_script_handles() {
			$handle = 'touchgrass-demo-pay-blocks';
			wp_register_script(
				$handle,
				TG_CORE_URL . 'assets/js/demo-pay-blocks.js',
				[ 'wc-blocks-registry', 'wc-settings', 'wp-element' ],
				TG_CORE_VERSION,
				true
			);
			return [ $handle ];
		}

		public function get_payment_method_data() {
			return [
				'title'       => $this->get_setting( 'title', __( 'Demo Pay (demo mode)', 'touchgrass-core' ) ),
				'description' => __( 'Demo mode: clicking “Place order” completes checkout instantly. No card, no charge, no grass shipped. Yet.', 'touchgrass-core' ),
				'supports'    => [ 'products' ],
			];
		}
	}

	add_action( 'woocommerce_blocks_payment_method_type_registration', function ( $registry ) {
		if ( ! tg_demo_mode() ) { return; }
		$registry->register( new TG_Demo_Pay_Blocks() );
	} );
} );
