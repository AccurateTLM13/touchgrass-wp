<?php
/**
 * Plugin Name: Touch Grass — Core
 * Description: Functionality for the Touch Grass store: content types (FAQ, testimonials, subscribers), product fields, demo payment gateway, one-click demo importer, and setup dashboard. Presentation lives in the Touch Grass theme.
 * Version: 2.0.0
 * Author: Bobby John Studio
 * Text Domain: touchgrass-core
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * License: GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'TG_CORE_VERSION', '2.0.0' );
define( 'TG_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'TG_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once TG_CORE_PATH . 'includes/class-tg-cpt.php';
require_once TG_CORE_PATH . 'includes/class-tg-product-meta.php';
require_once TG_CORE_PATH . 'includes/class-tg-importer.php';
require_once TG_CORE_PATH . 'includes/class-tg-admin.php';
require_once TG_CORE_PATH . 'includes/class-tg-newsletter.php';
require_once TG_CORE_PATH . 'includes/class-tg-demo-pay.php';

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
