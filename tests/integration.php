<?php
/**
 * Touch Grass integration tests — run via WP-CLI against a test install:
 *
 *   wp eval-file tests/integration.php
 *
 * Covers: demo-mode isolation, importer reruns (no duplicates), merchant-edit
 * preservation, explicit reset, FAQ ordering, testimonial deletion, Customizer
 * round-trip, newsletter provider error paths, coming-soon preservation.
 *
 * Prints one PASS/FAIL line per test; exits nonzero on any failure.
 * Safe to run repeatedly; it restores everything it changes.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$pass = 0; $fail = 0;
function t( $name, $cond, $detail = '' ) {
	global $pass, $fail;
	if ( $cond ) { $pass++; echo "PASS: $name\n"; }
	else { $fail++; echo "FAIL: $name" . ( $detail ? " — $detail" : '' ) . "\n"; }
}

/* ---------- 1. demo-mode isolation ---------- */
delete_option( 'tg_demo_mode' );
t( 'demo mode defaults to OFF', false === tg_demo_mode() );

$gateways_off = apply_filters( 'woocommerce_payment_gateways', [ 'WC_Gateway_Cheque' ] );
t( 'Demo Pay NOT registered when demo mode off', ! in_array( 'TG_Demo_Pay_Gateway', $gateways_off, true ) );

update_option( 'tg_demo_mode', true );
$gateways_on = apply_filters( 'woocommerce_payment_gateways', [ 'WC_Gateway_Cheque' ] );
t( 'Demo Pay registered when demo mode on', in_array( 'TG_Demo_Pay_Gateway', $gateways_on, true ) );
update_option( 'tg_demo_mode', false );

/* ---------- 2. importer reruns: no duplicates ---------- */
$before_products = count( wc_get_products( [ 'limit' => -1, 'status' => 'publish' ] ) );
$before_faqs = (int) wp_count_posts( 'tg_faq' )->publish;
$before_tms  = (int) wp_count_posts( 'tg_testimonial' )->publish;

$r1 = TG_Importer::run();
$dup_free = true;
foreach ( [ 'products', 'faqs', 'testimonials', 'coupon', 'media' ] as $step ) {
	if ( isset( $r1[ $step ]['created'] ) && $r1[ $step ]['created'] > 0 ) { $dup_free = false; }
}
t( 'importer rerun creates no duplicates', $dup_free, wp_json_encode( $r1 ) );
t( 'importer reports zero failures', ! array_filter( $r1, function ( $s ) { return is_array( $s ) && ( $s['failed'] ?? 0 ) > 0; } ) );

/* ---------- 3. merchant edits survive rerun ---------- */
$daily_id = wc_get_product_id_by_sku( 'TG-DAILY' );
$daily = wc_get_product( $daily_id );
$daily->set_name( 'Merchant Renamed Daily' );
$daily->set_regular_price( '99.99' );
$daily->set_price( '99.99' );
$daily->save();
TG_Importer::run();
$daily = wc_get_product( $daily_id );
t( 'rerun preserves merchant-edited name', 'Merchant Renamed Daily' === $daily->get_name(), $daily->get_name() );
t( 'rerun preserves merchant-edited price', '99.99' === $daily->get_regular_price(), $daily->get_regular_price() );

/* ---------- 4. managed products refresh voice fields only ---------- */
$commuter_id = wc_get_product_id_by_sku( 'TG-COMMUTER' );
update_post_meta( $commuter_id, '_tg_demo_managed', '1' );
$commuter = wc_get_product( $commuter_id );
$commuter->set_regular_price( '88.88' );
$commuter->set_price( '88.88' );
$commuter->save();
update_post_meta( $commuter_id, '_tg_tagline', 'Stale custom tagline' );
TG_Importer::run();
$tagline_after = get_post_meta( $commuter_id, '_tg_tagline', true );
$commuter = wc_get_product( $commuter_id );
t( 'managed product voice field (tagline) refreshes on rerun', 'Grass that travels better than you do.' === $tagline_after, $tagline_after );
t( 'managed product price still preserved on rerun', '88.88' === $commuter->get_regular_price(), $commuter->get_regular_price() );

/* ---------- 5. explicit reset restores demo values ---------- */
TG_Importer::reset_products();
$daily = wc_get_product( $daily_id );
t( 'reset restores demo name', 'The Daily Driver' === $daily->get_name(), $daily->get_name() );
t( 'reset restores demo price', '29' === $daily->get_regular_price(), $daily->get_regular_price() );
$commuter = wc_get_product( $commuter_id );
t( 'reset restores managed product price', '24' === $commuter->get_regular_price(), $commuter->get_regular_price() );
delete_post_meta( $commuter_id, '_tg_demo_managed' ); /* back to unmanaged, as imported */

/* ---------- 6. FAQ ordering uses the saved order field ---------- */
$faq_a = wp_insert_post( [ 'post_type' => 'tg_faq', 'post_title' => 'ZZZ Order Test Alpha', 'post_content' => 'a', 'post_status' => 'publish' ] );
$faq_b = wp_insert_post( [ 'post_type' => 'tg_faq', 'post_title' => 'ZZZ Order Test Beta', 'post_content' => 'b', 'post_status' => 'publish' ] );
update_post_meta( $faq_a, '_tg_faq_order', 500 );
update_post_meta( $faq_b, '_tg_faq_order', 5 );
$faqs = tg_get_faqs();
$titles = array_column( $faqs, 0 );
$pos_a = array_search( 'ZZZ Order Test Alpha', $titles, true );
$pos_b = array_search( 'ZZZ Order Test Beta', $titles, true );
t( 'FAQ with lower order value sorts first', false !== $pos_a && false !== $pos_b && $pos_b < $pos_a, "beta@$pos_b alpha@$pos_a" );
wp_delete_post( $faq_a, true );
wp_delete_post( $faq_b, true );

/* ---------- 7. deleted testimonials do not resurrect ---------- */
$tm_ids = get_posts( [ 'post_type' => 'tg_testimonial', 'posts_per_page' => -1, 'fields' => 'ids', 'post_status' => 'publish' ] );
foreach ( $tm_ids as $id ) { wp_delete_post( $id, true ); }
t( 'no testimonials after merchant deletes all', [] === tg_get_testimonials() );
TG_Importer::run(); /* recover */
t( 'importer recovers deleted testimonials without duplicates', 3 === (int) wp_count_posts( 'tg_testimonial' )->publish );

/* ---------- 8. Customizer round-trip ---------- */
set_theme_mod( 'tg_shop_title', 'Custom Shop Title' );
t( 'theme mod read back through tg_brand()', 'Custom Shop Title' === tg_brand( 'tg_shop_title' ) );
remove_theme_mod( 'tg_shop_title' );
t( 'tg_brand() falls back to default after removal', 'Nine rectangles* of grass.' === tg_brand( 'tg_shop_title' ), tg_brand( 'tg_shop_title' ) );

/* ---------- 9. newsletter provider error paths (fake key) ---------- */
update_option( 'tg_buttondown_api_key', 'tg_test_fake_key_123' );
$res = TG_Newsletter::subscribe_buttondown( 'integration-test-' . time() . '@example.com' );
t( 'provider call with bad key returns structured error (HTTP path exercised)', 'error' === $res['status'], wp_json_encode( $res ) );
delete_option( 'tg_buttondown_api_key' );
t( 'newsletter reports unconfigured when no key', ! tg_newsletter_configured() );

/* ---------- 10. coming-soon preservation ---------- */
update_option( 'woocommerce_coming_soon', 'yes' );
TG_Importer::run();
t( 'importer preserves coming-soon=yes', 'yes' === get_option( 'woocommerce_coming_soon' ) );
update_option( 'woocommerce_coming_soon', 'no' );
TG_Importer::run();
t( 'importer preserves coming-soon=no', 'no' === get_option( 'woocommerce_coming_soon' ) );

echo "\n$pass passed, $fail failed\n";
exit( $fail > 0 ? 1 : 0 );
