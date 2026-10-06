<?php
/**
 * The Grass Club: four tiers of belonging. The grass keeps score.
 *
 * Tiers are computed honestly from real data — completed order count,
 * lifetime net spend on completed orders, and the local newsletter
 * subscriber record. No tier is ever invented.
 *
 *   prospect — no account, or an account the Grassworks has no record of.
 *   seedling — newsletter subscriber, zero completed orders.
 *   sod      — at least one completed order.
 *   estate   — five or more completed orders, or $200+ lifetime spend.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * All club tiers: slug => [ name, benefit ].
 *
 * @return array
 */
function tg_club_tiers() {
	return [
		'prospect' => [
			'name'    => __( 'Prospect', 'touchgrass-core' ),
			'benefit' => __( 'Unregistered. The Grassworks does not know you exist.', 'touchgrass-core' ),
		],
		'seedling' => [
			'name'    => __( 'Seedling', 'touchgrass-core' ),
			'benefit' => __( 'Rare (but invoiced) emails. First cut of limited batches.', 'touchgrass-core' ),
		],
		'sod'      => [
			'name'    => __( 'Sod', 'touchgrass-core' ),
			'benefit' => __( 'Priority harvest allocation. Your plots ship first. Allegedly.', 'touchgrass-core' ),
		],
		'estate'   => [
			'name'    => __( 'Estate', 'touchgrass-core' ),
			'benefit' => __( 'Annual inspection waived. A brass plaque bearing your name.', 'touchgrass-core' ),
		],
	];
}

/**
 * A member's current tier, computed from real records.
 *
 * @param int $user_id WordPress user ID. 0 or unknown => prospect.
 * @return array [ 'slug' => ..., 'name' => ..., 'benefit' => ... ]
 */
function tg_grass_club_tier( $user_id ) {
	$tiers   = tg_club_tiers();
	$user_id = (int) $user_id;
	$make    = function ( $slug ) use ( $tiers ) {
		return [
			'slug'    => $slug,
			'name'    => $tiers[ $slug ]['name'],
			'benefit' => $tiers[ $slug ]['benefit'],
		];
	};

	if ( $user_id <= 0 || ! function_exists( 'wc_get_orders' ) ) {
		return $make( 'prospect' );
	}
	$user = get_user_by( 'id', $user_id );
	if ( ! $user ) {
		return $make( 'prospect' );
	}

	$count = 0;
	$spent = 0.0;
	$orders = wc_get_orders( [
		'customer_id' => $user_id,
		'status'      => 'completed',
		'limit'       => -1,
		'return'      => 'ids',
	] );
	foreach ( (array) $orders as $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order ) { continue; }
		$count++;
		/* Net retained spend: partial refunds move the member back down. */
		$spent += (float) $order->get_total() - (float) $order->get_total_refunded();
	}

	if ( $count >= 5 || $spent >= 200 ) {
		return $make( 'estate' );
	}
	if ( $count >= 1 ) {
		return $make( 'sod' );
	}

	/* Newsletter subscriber, zero completed orders. The local subscriber
	 * record lives at post slug sub-{md5(email)}. */
	$slug = 'sub-' . md5( strtolower( trim( (string) $user->user_email ) ) );
	if ( get_page_by_path( $slug, OBJECT, 'tg_subscriber' ) ) {
		return $make( 'seedling' );
	}

	return $make( 'prospect' );
}
