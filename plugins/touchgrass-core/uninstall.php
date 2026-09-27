<?php
/**
 * Uninstall: remove the gateway's stored settings.
 * Demo content (products, FAQs, testimonials, subscribers, menu, coupon)
 * is intentionally LEFT in place — deleting a merchant's catalogue on
 * uninstall would be rude. Remove it manually if you want a clean slate.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

delete_option( 'woocommerce_touchgrass_demo_settings' );
