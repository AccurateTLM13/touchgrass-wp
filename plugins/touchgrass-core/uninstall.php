<?php
/**
 * Uninstall: remove the plugin's own settings.
 * Demo content (products, FAQs, testimonials, subscribers, menu, coupon)
 * is intentionally LEFT in place — deleting a merchant's catalogue on
 * uninstall would be rude. Remove it manually if you want a clean slate.
 * Subscriber records (tg_subscriber posts) are also left in place; delete
 * them from Touch Grass → Subscribers if your retention policy requires it.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }

delete_option( 'woocommerce_touchgrass_demo_settings' );
delete_option( 'tg_demo_mode' );
delete_option( 'tg_buttondown_api_key' );
delete_option( 'tg_demo_imported' );
delete_option( 'tg_legacy_demo_pay_blocked' );
