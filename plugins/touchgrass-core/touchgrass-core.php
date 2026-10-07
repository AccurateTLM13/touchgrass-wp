<?php
/**
 * Plugin Name: Touch Grass — Core
 * Description: Functionality for the Touch Grass store: content types (FAQ, testimonials, subscribers), product fields, demo payment gateway, one-click demo importer, newsletter signup, and setup dashboard. Presentation lives in the Touch Grass theme.
 * Version: 2.6.0
 * Author: Bobby John Studio
 * Text Domain: touchgrass-core
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * Requires PHP: 8.1
 * Requires WP: 6.5
 * License: GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'TG_CORE_VERSION', '2.1.0' );
define( 'TG_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'TG_CORE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Demo mode: the single switch that makes this a demo store instead of a
 * production store. OFF by default. When off:
 *
 * - The Demo Pay mock gateway is never registered.
 * - No demo notices appear on cart/checkout.
 * - No demo payment hints appear anywhere.
 *
 * When on, the dashboard says so loudly and every mock surface is labeled.
 *
 * @return bool
 */
function tg_demo_mode() {
	return (bool) get_option( 'tg_demo_mode', false );
}

/**
 * Newsletter configured: a mailing-provider API key is stored.
 *
 * @return bool
 */
function tg_newsletter_configured() {
	return (bool) get_option( 'tg_buttondown_api_key', '' );
}

/**
 * Legacy standalone Demo Pay plugin (touchgrass-demo-pay/) was superseded by
 * the gateway inside this plugin in v2. If an old installation still has it
 * active, loading it alongside this plugin would redeclare
 * TG_Demo_Pay_Gateway and fatal. Suppress it before it loads and tell the
 * admin how to remove it for good.
 */
add_filter( 'option_active_plugins', 'tg_suppress_legacy_demo_pay' );
function tg_suppress_legacy_demo_pay( $plugins ) {
	$legacy = 'touchgrass-demo-pay/touchgrass-demo-pay.php';
	if ( is_array( $plugins ) && in_array( $legacy, $plugins, true ) ) {
		update_option( 'tg_legacy_demo_pay_blocked', 1 );
		return array_values( array_diff( $plugins, [ $legacy ] ) );
	}
	return $plugins;
}

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'activate_plugins' ) ) { return; }
	if ( get_option( 'tg_legacy_demo_pay_blocked' ) ) {
		$deactivate_url = wp_nonce_url(
			add_query_arg(
				[ 'action' => 'deactivate', 'plugin' => 'touchgrass-demo-pay/touchgrass-demo-pay.php' ],
				admin_url( 'plugins.php' )
			),
			'deactivate-plugin_touchgrass-demo-pay/touchgrass-demo-pay.php'
		);
		echo '<div class="notice notice-warning"><p>'
			. esc_html__( 'Touch Grass: the legacy standalone “Touch Grass — Demo Pay” plugin is installed. It has been superseded by the Demo Pay gateway inside Touch Grass — Core and was prevented from loading (keeping both active would crash the site).', 'touchgrass-core' )
			. ' <a href="' . esc_url( $deactivate_url ) . '">'
			. esc_html__( 'Deactivate it', 'touchgrass-core' )
			. '</a> '
			. esc_html__( 'and then delete it — it is no longer part of the supported installation.', 'touchgrass-core' )
			. '</p></div>';
	}
} );

require_once TG_CORE_PATH . 'includes/class-tg-cpt.php';
require_once TG_CORE_PATH . 'includes/class-tg-product-meta.php';
require_once TG_CORE_PATH . 'includes/class-tg-importer.php';
require_once TG_CORE_PATH . 'includes/class-tg-settings.php';
require_once TG_CORE_PATH . 'includes/class-tg-microcopy.php';
require_once TG_CORE_PATH . 'includes/class-tg-admin.php';
require_once TG_CORE_PATH . 'includes/class-tg-newsletter.php';
require_once TG_CORE_PATH . 'includes/class-tg-demo-pay.php';
require_once TG_CORE_PATH . 'includes/tg-club.php';

/**
 * Everything here degrades gracefully when WooCommerce is inactive:
 * content types and the newsletter still work; the importer, gateway,
 * and product fields wait for WooCommerce and say so in the dashboard.
 */
function tg_core_woo_active() {
	return class_exists( 'WooCommerce', false );
}

add_action( 'admin_notices', function () {
	if ( tg_core_woo_active() ) { return; }
	if ( ! current_user_can( 'activate_plugins' ) ) { return; }
	echo '<div class="notice notice-warning"><p>'
		. esc_html__( 'Touch Grass — Core is active, but WooCommerce is not. The demo importer, Demo Pay gateway, and product fields need WooCommerce to do their jobs.', 'touchgrass-core' )
		. '</p></div>';
} );
