<?php
/**
 * Plugin Name: Touch Grass — Demo Pay
 * Description: A mock payment gateway for the Touch Grass demo store. Completes orders without charging anything.
 * Version: 1.0.0
 * Author: Bobby John Studio
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_filter( 'woocommerce_payment_gateways', function ( $gateways ) {
	$gateways[] = 'TG_Demo_Pay_Gateway';
	return $gateways;
} );

add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) { return; }

	class TG_Demo_Pay_Gateway extends WC_Payment_Gateway {
		public function __construct() {
			$this->id                 = 'tg_demo_pay';
			$this->icon               = '';
			$this->has_fields         = false;
			$this->method_title       = __( 'Demo Pay', 'touchgrass' );
			$this->method_description = __( 'Mock checkout for the demo store. No real payment is processed.', 'touchgrass' );
			$this->title              = __( 'Demo Pay', 'touchgrass' );
			$this->description        = __( 'This is a demo store. Clicking “Place order” completes the order instantly — no card, no charge, no actual grass shipped. Yet.', 'touchgrass' );
			$this->init_form_fields();
			$this->init_settings();
			$this->enabled = $this->get_option( 'enabled', 'yes' );
			$this->title   = $this->get_option( 'title', $this->title );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		}

		public function init_form_fields() {
			$this->form_fields = [
				'enabled' => [
					'title'   => __( 'Enable/Disable', 'woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Demo Pay', 'touchgrass' ),
					'default' => 'yes',
				],
				'title' => [
					'title'       => __( 'Title', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Name shown at checkout.', 'woocommerce' ),
					'default'     => __( 'Demo Pay', 'touchgrass' ),
				],
			];
		}

		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );
			// Mock: mark paid and complete immediately.
			$order->payment_complete();
			$order->add_order_note( __( 'Demo Pay: mock payment accepted. No real charge made.', 'touchgrass' ) );
			WC()->cart->empty_cart();
			return [
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			];
		}
	}
} );

/* Extra blurb under the gateway at checkout. */
add_action( 'woocommerce_after_payment_gateways', function () {
	echo '<p class="tg-gateway-blurb">' . esc_html__( 'Demo store: the grass is real, the charge is not.', 'touchgrass' ) . '</p>';
} );
