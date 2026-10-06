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

$GLOBALS['tg_pass'] = 0; $GLOBALS['tg_fail'] = 0;
function t( $name, $cond, $detail = '' ) {
	if ( $cond ) { $GLOBALS['tg_pass']++; echo "PASS: $name\n"; }
	else { $GLOBALS['tg_fail']++; echo "FAIL: $name" . ( $detail ? " — $detail" : '' ) . "\n"; }
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

/* ---------- 11. RC2: Customizer image controls store attachment IDs ---------- */
require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
require_once ABSPATH . WPINC . '/class-wp-customize-control.php';
require_once ABSPATH . WPINC . '/customize/class-wp-customize-media-control.php';
require_once ABSPATH . WPINC . '/customize/class-wp-customize-upload-control.php';
require_once ABSPATH . WPINC . '/customize/class-wp-customize-image-control.php';
global $wp_customize;
$wp_customize = new WP_Customize_Manager();
do_action( 'customize_register', $wp_customize );
$hero_control = $wp_customize->get_control( 'tg_hero_image' );
$how_control  = $wp_customize->get_control( 'tg_how_image' );
t(
	'hero image control is Media_Control (ID storage), not Image_Control (URL storage)',
	$hero_control instanceof WP_Customize_Media_Control && ! $hero_control instanceof WP_Customize_Image_Control,
	is_object( $hero_control ) ? get_class( $hero_control ) : 'missing'
);
t(
	'how-it-works image control is Media_Control (ID storage)',
	$how_control instanceof WP_Customize_Media_Control && ! $how_control instanceof WP_Customize_Image_Control,
	is_object( $how_control ) ? get_class( $how_control ) : 'missing'
);
t( 'rating sanitizer keeps half-stars', 4.5 === tg_sanitize_rating( '4.7' ) );
t( 'rating sanitizer clamps to 0–5', 5 == tg_sanitize_rating( 9 ) && 0 == tg_sanitize_rating( -2 ) );

/* ---------- 12. RC2: trust-row stars match the number shown ---------- */
$daily_id = wc_get_product_id_by_sku( 'TG-DAILY' );
update_post_meta( $daily_id, '_wc_average_rating', '4.0' );
update_post_meta( $daily_id, '_wc_review_count', '10' );
$live = tg_live_rating_data();
t(
	'live rating aggregate returns real avg + count',
	is_array( $live ) && abs( $live['avg'] - 4.0 ) < 0.01 && 10 === $live['count'],
	wp_json_encode( $live )
);
t( 'live rating text uses real numbers', false !== strpos( tg_live_rating_text(), '4.0' ), tg_live_rating_text() );
t( 'stars render fractional ratings honestly', '★★★★⯪' === wp_strip_all_tags( tg_stars( 4.5 ) ), wp_strip_all_tags( tg_stars( 4.5 ) ) );
delete_post_meta( $daily_id, '_wc_average_rating' );
delete_post_meta( $daily_id, '_wc_review_count' );
t( 'live rating is empty with no reviews', null === tg_live_rating_data() && '' === tg_live_rating_text() );

/* ---------- 13. RC2: no forced newsletter tags (free-plan safe) ---------- */
t( 'newsletter sends no tags by default', [] === apply_filters( 'tg_newsletter_tags', [] ) );
$tg_tag_filter = function () { return [ 'vip' ]; };
add_filter( 'tg_newsletter_tags', $tg_tag_filter );
t( 'newsletter tags are opt-in via filter', [ 'vip' ] === apply_filters( 'tg_newsletter_tags', [] ) );
remove_filter( 'tg_newsletter_tags', $tg_tag_filter );

/* ---------- 14. RC2: stats have no fictional defaults; importer fills them as demo ---------- */
$fields = tg_brand_fields();
t( 'stat 1 default is empty (no fictional reviews claim)', '' === $fields['tg_stat_1_value']['default'] );
t( 'stat 2 default is empty (no fictional rating claim)', '' === $fields['tg_stat_2_value']['default'] );
remove_theme_mod( 'tg_stat_1_value' );
remove_theme_mod( 'tg_stat_2_value' );
remove_theme_mod( 'tg_proof_title' );
TG_Importer::run();
t( 'importer fills empty stats as demo material', '8,600+' === get_theme_mod( 'tg_stat_1_value' ), get_theme_mod( 'tg_stat_1_value' ) );
t( 'importer flags demo stats as set', (bool) get_option( 'tg_demo_stats_set' ) );
set_theme_mod( 'tg_stat_1_value', 'Merchant stat' );
TG_Importer::run();
t( 'importer rerun preserves merchant stat edits', 'Merchant stat' === get_theme_mod( 'tg_stat_1_value' ), get_theme_mod( 'tg_stat_1_value' ) );
remove_theme_mod( 'tg_stat_1_value' ); /* restore demo state */
TG_Importer::run();

/* ---------- 15. v2.2.0: microcopy map defaults + override ---------- */
t( 'microcopy default add_to_cart', 'Claim Your Plot' === tg_microcopy( 'add_to_cart' ), tg_microcopy( 'add_to_cart' ) );
t( 'microcopy default empty_cart', 'Nothing here. Like your step count.' === tg_microcopy( 'empty_cart' ) );
t( 'microcopy default checkout_button', 'Complete Invoice' === tg_microcopy( 'checkout_button' ) );
t( 'microcopy default order_received_title', 'Invoice paid. Grass dispatched.' === tg_microcopy( 'order_received_title' ) );
t( 'microcopy unknown key returns empty', '' === tg_microcopy( 'nope_not_a_key' ) );
update_option( 'tg_microcopy_coupon_label', 'Merchant bribe label' );
t( 'microcopy override wins', 'Merchant bribe label' === tg_microcopy( 'coupon_label' ), tg_microcopy( 'coupon_label' ) );
update_option( 'tg_microcopy_coupon_label', '' );
t( 'microcopy empty override falls back to default', 'Bribe code' === tg_microcopy( 'coupon_label' ) );
delete_option( 'tg_microcopy_coupon_label' );

/* ---------- 16. v2.2.0: checkout button + email heading wired to map ---------- */
t( 'checkout button uses microcopy map', 'Complete Invoice' === apply_filters( 'woocommerce_order_button_text', 'Place order' ) );
t( 'processing email heading is the deed', 'Deed of Grass Conveyance' === apply_filters( 'woocommerce_email_heading_customer_processing_order', 'Thank you' ) );

/* ---------- 17. v2.2.0: deed tab + deed data ---------- */
$deed_tabs = apply_filters( 'woocommerce_product_tabs', [] );
t( 'deed tab registered', isset( $deed_tabs['tg_deed'] ) && 'The Deed' === $deed_tabs['tg_deed']['title'] );
$deed_rows = tg_deed_data( $daily_id );
$deed_map = [];
foreach ( $deed_rows as $row ) { $deed_map[ $row[0] ] = $row[1]; }
t( 'deed has five spec rows', 5 === count( $deed_rows ), count( $deed_rows ) );
t( 'deed provenance default', 'Plot 7, Surrey Grassworks' === $deed_map['Provenance'], $deed_map['Provenance'] ?? '' );
t( 'deed warranty default', 'Photosynthesis guaranteed for 30 days.' === $deed_map['Warranty'] );
$gnome_id = wc_get_product_id_by_sku( 'TG-GNOME' );
$gnome_deed = tg_deed_data( $gnome_id );
$gnome_map = [];
foreach ( $gnome_deed as $row ) { $gnome_map[ $row[0] ] = $row[1]; }
t( 'gnome deed is ceramic, not grass', '0 — ceramic' === $gnome_map['Blade count'], $gnome_map['Blade count'] ?? '' );
t( 'per-blade line computes from price', 0 === strpos( tg_per_blade_line( wc_get_product( $daily_id ) ), '≈ $' ), tg_per_blade_line( wc_get_product( $daily_id ) ) );

/* ---------- 18. v2.2.0: importer sets 8 FAQs, batch meta, cross-sells ---------- */
$faq_count = count( get_posts( [ 'post_type' => 'tg_faq', 'post_status' => 'publish', 'numberposts' => -1 ] ) );
t( 'importer maintains 8 FAQs', 8 === $faq_count, $faq_count );
TG_Importer::run();
$faq_count2 = count( get_posts( [ 'post_type' => 'tg_faq', 'post_status' => 'publish', 'numberposts' => -1 ] ) );
t( 'FAQ count stable across reruns', 8 === $faq_count2, $faq_count2 );
t( 'batch meta set on plots', 'Batch No. 7' === tg_product_batch( $daily_id ), tg_product_batch( $daily_id ) );
t( 'batch meta empty on accessories', '' === tg_product_batch( $gnome_id ) );
$daily_xs = wc_get_product( $daily_id )->get_cross_sell_ids();
$mister_id = wc_get_product_id_by_sku( 'TG-MISTER' );
t( 'plots cross-sell the mister', in_array( $mister_id, array_map( 'intval', $daily_xs ), true ) );
t( 'plots cross-sell the gnome', in_array( (int) $gnome_id, array_map( 'intval', $daily_xs ), true ) );

/* ---------- 19. v2.3.0: deed institutional hierarchy ---------- */
$hier = tg_deed_hierarchy( $daily_id );
t( 'deed corporation default', 'Surrey Grassworks' === $hier['corporation'][1], $hier['corporation'][1] ?? '' );
t( 'deed division default', 'North Greenhouse Division' === $hier['division'][1], $hier['division'][1] ?? '' );
t( 'deed program default', 'Indoor Recreation Commodity Program' === $hier['program'][1], $hier['program'][1] ?? '' );
t( 'deed harvest default', 'Harvest 07' === $hier['harvest'][1], $hier['harvest'][1] ?? '' );
t( 'deed authorization default', 'Authorized Indoor Use Only' === $hier['authorization'][1], $hier['authorization'][1] ?? '' );
t( 'deed classification default', 'Fescue Classification: Executive Desk Grade' === $hier['classification'][1], $hier['classification'][1] ?? '' );
t( 'daily plot assigned by importer', 'Plot 184-C' === $hier['plot'][1], $hier['plot'][1] ?? '' );

$night_id = wc_get_product_id_by_sku( 'TG-NIGHT' );
$night_hier = tg_deed_hierarchy( $night_id );
t( 'night shift gets subterranean division', 'Subterranean Division' === $night_hier['division'][1], $night_hier['division'][1] ?? '' );
t( 'night shift plot 13-D', 'Plot 13-D' === $night_hier['plot'][1], $night_hier['plot'][1] ?? '' );
t( 'night shift cave dweller classification', 'Shade-Tolerance Classification: Cave Dweller Grade' === $night_hier['classification'][1] );

$gnome_hier = tg_deed_hierarchy( $gnome_id );
t( 'gnome ornamental division', 'Ornamental Division' === $gnome_hier['division'][1], $gnome_hier['division'][1] ?? '' );
t( 'gnome lot not harvest', 'Lot 3' === $gnome_hier['harvest'][1], $gnome_hier['harvest'][1] ?? '' );
t( 'gnome unit 3-C', 'Unit 3-C' === $gnome_hier['plot'][1], $gnome_hier['plot'][1] ?? '' );
t( 'gnome ceramic classification', 'Ceramic Classification: Ornamental' === $gnome_hier['classification'][1] );

$mister_hier = tg_deed_hierarchy( $mister_id );
t( 'mister hydration division', 'Hydration Apparatus Division' === $mister_hier['division'][1], $mister_hier['division'][1] ?? '' );

/* Plot fallback: a product with no plot meta gets deterministic Plot {id}-A. */
$fallback_id = wp_insert_post( [ 'post_title' => 'TG Test Fallback', 'post_type' => 'product', 'post_status' => 'draft' ] );
$fallback_hier = tg_deed_hierarchy( $fallback_id );
t( 'plot fallback is deterministic', 'Plot ' . $fallback_id . '-A' === $fallback_hier['plot'][1], $fallback_hier['plot'][1] ?? '' );
wp_delete_post( $fallback_id, true );

/* ---------- 20. v2.3.0: registry numbers + conveyance microcopy ---------- */
t( 'registry pads to five digits', 'TG-00042' === tg_plot_registry_number( 42 ), tg_plot_registry_number( 42 ) );
t( 'registry single digit', 'TG-00007' === tg_plot_registry_number( 7 ) );
t( 'conveyance title default', 'Official Notice of Conveyance' === tg_microcopy( 'conveyance_title' ) );
t( 'conveyance text has placeholders', false !== strpos( tg_microcopy( 'conveyance_text' ), '{registry}' ) && false !== strpos( tg_microcopy( 'conveyance_text' ), '{name}' ) );
t( 'email conveyance has placeholders', false !== strpos( tg_microcopy( 'email_conveyance' ), '{registry}' ) && false !== strpos( tg_microcopy( 'email_conveyance' ), '{division}' ) );

/* Thank-you conveyance block renders with registry + name. */
$conv_order = wc_create_order();
$conv_order->set_billing_first_name( 'Testy' );
$conv_order->set_billing_last_name( 'McTest' );
$conv_order->add_product( wc_get_product( $daily_id ), 1 );
$conv_order->calculate_totals();
$conv_order->save();
$conv_id = $conv_order->get_id();
ob_start();
do_action( 'woocommerce_thankyou', $conv_id );
$conv_html = ob_get_clean();
t( 'thankyou shows conveyance title', false !== strpos( $conv_html, 'Official Notice of Conveyance' ) );
t( 'thankyou shows registry number', false !== strpos( $conv_html, tg_plot_registry_number( $conv_id ) ), tg_plot_registry_number( $conv_id ) );
t( 'thankyou names the owner', false !== strpos( $conv_html, 'Testy McTest' ) );
wp_delete_post( $conv_id, true );

/* Email conveyance hook is registered for the processing email. */
t( 'email conveyance hook registered', false !== has_action( 'woocommerce_email_after_order_table' ) );

/* ---------- 21. v2.3.0: grass club tiers ---------- */
$tiers = tg_club_tiers();
t( 'four tiers defined', 4 === count( $tiers ) && isset( $tiers['prospect'], $tiers['seedling'], $tiers['sod'], $tiers['estate'] ) );
t( 'unknown user is prospect', 'prospect' === tg_grass_club_tier( 0 )['slug'] );
t( 'nonexistent user is prospect', 'prospect' === tg_grass_club_tier( 999999 )['slug'] );

$club_uid = wp_insert_user( [ 'user_login' => 'tg_club_test', 'user_email' => 'tgclubtest@example.com', 'user_pass' => wp_generate_password() ] );
t( 'fresh account is prospect', 'prospect' === tg_grass_club_tier( $club_uid )['slug'], tg_grass_club_tier( $club_uid )['slug'] );

/* Seedling: subscriber record, zero orders. */
$sub_slug = 'sub-' . md5( 'tgclubtest@example.com' );
$sub_id = wp_insert_post( [ 'post_title' => 'tgclubtest@example.com', 'post_name' => $sub_slug, 'post_type' => 'tg_subscriber', 'post_status' => 'publish' ] );
t( 'subscriber with no orders is seedling', 'seedling' === tg_grass_club_tier( $club_uid )['slug'] );

$make_order = function ( $uid, $sku, $qty ) {
	$order = wc_create_order();
	$order->set_customer_id( $uid );
	$order->set_billing_email( 'tgclubtest@example.com' );
	$order->add_product( wc_get_product( wc_get_product_id_by_sku( $sku ) ), $qty );
	$order->calculate_totals();
	$order->set_status( 'completed' );
	$order->save();
	return $order->get_id();
};

/* Sod: one completed order. */
$sod_oid = $make_order( $club_uid, 'TG-DAILY', 1 );
t( 'one completed order is sod', 'sod' === tg_grass_club_tier( $club_uid )['slug'] );

/* Estate by count: five completed orders (5 x $29 = $145, under the spend bar). */
$estate_oids = [ $sod_oid ];
for ( $i = 0; $i < 4; $i++ ) { $estate_oids[] = $make_order( $club_uid, 'TG-DAILY', 1 ); }
t( 'five completed orders is estate', 'estate' === tg_grass_club_tier( $club_uid )['slug'] );
foreach ( $estate_oids as $oid ) { wp_delete_post( $oid, true ); }

/* Estate by spend: 4 x $59 = $236 with only four orders. */
$spend_oids = [];
for ( $i = 0; $i < 4; $i++ ) { $spend_oids[] = $make_order( $club_uid, 'TG-PROMAX', 1 ); }
t( 'high spend is estate', 'estate' === tg_grass_club_tier( $club_uid )['slug'] );
foreach ( $spend_oids as $oid ) { wp_delete_post( $oid, true ); }

/* Back to seedling once orders are gone. */
t( 'tier drops back to seedling', 'seedling' === tg_grass_club_tier( $club_uid )['slug'] );
wp_delete_post( $sub_id, true );
wp_delete_user( $club_uid );

/* ---------- v2.4.0: SEO + social ---------- */
$og_default = tg_og_image_default();
t( 'og default is absolute theme asset URL', 0 === strpos( $og_default, 'http' ) && 'og-default.jpg' === basename( strtok( $og_default, '?' ) ), $og_default );

/* Simulate a product page for is_product()-gated helpers. */
$seo_pid  = wc_get_product_id_by_sku( 'TG-DAILY' );
$seo_prod = wc_get_product( $seo_pid );
global $wp_query, $post;
$seo_old_query = $wp_query;
$seo_old_post  = $post ?? null;
$wp_query = new WP_Query( [ 'post_type' => 'product', 'p' => $seo_pid ] );
$wp_query->the_post();

t( 'is_product true in simulated context', is_product() );

$og_data = tg_og_image_data();
t( 'product og image overrides default', false !== strpos( $og_data[0], 'uploads' ) && false === strpos( $og_data[0], 'og-default.jpg' ), $og_data[0] );
t( 'product og image has dimensions', $og_data[1] > 0 && $og_data[2] > 0 );

$seo_desc = tg_meta_description();
t( 'product meta description non-empty', '' !== $seo_desc, substr( $seo_desc, 0, 60 ) );
t( 'product meta description within ~160 chars', mb_strlen( $seo_desc ) <= 165, (string) mb_strlen( $seo_desc ) );

$schema = tg_product_schema();
t( 'product schema has name', ( $schema['name'] ?? '' ) === $seo_prod->get_name() );
t( 'product schema offers price matches', (string) ( $schema['offers']['price'] ?? '' ) === (string) $seo_prod->get_price(), (string) ( $schema['offers']['price'] ?? '?' ) );
t( 'product schema availability honest', in_array( $schema['offers']['availability'] ?? '', [ 'https://schema.org/InStock', 'https://schema.org/OutOfStock' ], true ) );
t( 'product schema brand is Surrey Grassworks', ( $schema['brand']['name'] ?? '' ) === 'Surrey Grassworks' );
t( 'product schema JSON-encodes', null !== json_decode( wp_json_encode( $schema ) ) );

ob_start();
tg_seo_head();
$seo_head = ob_get_clean();
t( 'head has og:image', false !== strpos( $seo_head, 'property="og:image"' ) );
t( 'head has twitter:image', false !== strpos( $seo_head, 'name="twitter:image"' ) );
t( 'head has meta description', false !== strpos( $seo_head, 'name="description"' ) );
t( 'head does NOT duplicate og:title', false === strpos( $seo_head, 'property="og:title"' ) );
t( 'head does NOT duplicate twitter:card', false === strpos( $seo_head, 'name="twitter:card"' ) );

/* Restore query; homepage schema + fallback description need no product. */
$wp_query = $seo_old_query;
if ( $seo_old_post ) { $post = $seo_old_post; } else { unset( $post ); }
wp_reset_postdata();

$fallback_desc = tg_meta_description();
t( 'meta description fallback non-empty', '' !== $fallback_desc );

echo "\n" . $GLOBALS['tg_pass'] . " passed, " . $GLOBALS['tg_fail'] . " failed\n";
exit( $GLOBALS['tg_fail'] > 0 ? 1 : 0 );
