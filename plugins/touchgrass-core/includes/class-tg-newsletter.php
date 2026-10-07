<?php
/**
 * Newsletter signup, captured locally with optional Buttondown delivery.
 *
 * AJAX action: tg_newsletter_subscribe (nonce: tg_newsletter_nonce, action 'tg_newsletter').
 * A second action, tg_newsletter_nonce_refresh, hands the theme JS a fresh
 * nonce so an expired nonce on a long-cached page degrades to one silent
 * retry instead of an error.
 *
 * Abuse protection: honeypot field, per-IP rate limiting (5 attempts/hour),
 * nonce verification.
 *
 * Every signup is stored as a private local tg_subscriber record (email,
 * consent timestamp, source, provider) — the server is the system of record,
 * and the Subscribers section of the Touch Grass dashboard exports them as
 * CSV. When a Buttondown API key is configured the signup is ALSO delivered
 * to the Buttondown audience; a provider failure never loses the signup, it
 * is still captured locally and the error is logged.
 *
 * Unsubscribing happens through Buttondown (its emails carry unsubscribe
 * links); deleting the local record is documented in docs/NEWSLETTER.md.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class TG_Newsletter {

	const API_BASE = 'https://api.buttondown.com/v1';
	const RATE_LIMIT = 5; /* attempts per IP per hour */

	public static function init() {
		add_action( 'wp_ajax_tg_newsletter_subscribe', [ __CLASS__, 'handle' ] );
		add_action( 'wp_ajax_nopriv_tg_newsletter_subscribe', [ __CLASS__, 'handle' ] );
		add_action( 'wp_ajax_tg_newsletter_nonce_refresh', [ __CLASS__, 'refresh_nonce' ] );
		add_action( 'wp_ajax_nopriv_tg_newsletter_nonce_refresh', [ __CLASS__, 'refresh_nonce' ] );
	}

	public static function refresh_nonce() {
		wp_send_json_success( [ 'nonce' => wp_create_nonce( 'tg_newsletter' ) ] );
	}

	protected static function rate_key() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
		return 'tg_nl_' . md5( $ip );
	}

	protected static function rate_limited() {
		$count = (int) get_transient( self::rate_key() );
		if ( $count >= self::RATE_LIMIT ) { return true; }
		set_transient( self::rate_key(), $count + 1, HOUR_IN_SECONDS );
		return false;
	}

	public static function handle() {
		/* Nonce: distinguish "expired" so the client can refresh and retry. */
		$nonce = isset( $_POST['tg_newsletter_nonce'] ) ? sanitize_key( $_POST['tg_newsletter_nonce'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'tg_newsletter' ) ) {
			wp_send_json_error( [
				'message' => __( 'Your session expired. Trying again with a fresh one…', 'touchgrass-core' ),
				'code'    => 'expired_nonce',
			] );
		}

		/* Honeypot: bots fill it, humans never see it. */
		$honeypot = isset( $_POST['tg_company'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['tg_company'] ) ) ) : '';
		if ( '' !== $honeypot ) {
			/* Pretend success — no reason to tell a bot it was caught. */
			wp_send_json_success( [ 'message' => function_exists( 'tg_microcopy' ) ? tg_microcopy( 'newsletter_success' ) : __( 'You are on the list. The first invoice is being prepared.', 'touchgrass-core' ) ] );
		}

		if ( self::rate_limited() ) {
			wp_send_json_error( [ 'message' => __( 'Too many attempts. Give it an hour — the grass is patient.', 'touchgrass-core' ) ] );
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			wp_send_json_error( [ 'message' => __( 'That email doesn’t look right. Try again?', 'touchgrass-core' ) ] );
		}

		$source = isset( $_POST['source'] ) ? sanitize_key( $_POST['source'] ) : 'site';

		/* Local duplicate check first: cheap, no API call. */
		$slug = 'sub-' . md5( strtolower( $email ) );
		if ( get_page_by_path( $slug, OBJECT, 'tg_subscriber' ) ) {
			wp_send_json_error( [ 'message' => __( 'You’re already on the list. The grass remembers.', 'touchgrass-core' ) ] );
		}

		$provider    = 'local';
		$external_id = '';

		if ( tg_newsletter_configured() ) {
			$result = self::subscribe_buttondown( $email );

			if ( 'subscribed' === $result['status'] ) {
				$provider    = 'buttondown';
				$external_id = $result['id'];
			} elseif ( 'duplicate' === $result['status'] ) {
				/* Provider says already subscribed: keep the local record in sync. */
				self::record_local( $email, $slug, 'buttondown', '', $source );
				wp_send_json_error( [ 'message' => __( 'You’re already on the list. The grass remembers.', 'touchgrass-core' ) ] );
			} else {
				/* Provider error: never lose the signup — capture locally anyway. */
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( 'Touch Grass newsletter error: ' . $result['detail'] );
				}
			}
		}

		self::record_local( $email, $slug, $provider, $external_id, $source );
		wp_send_json_success( [
			'message' => function_exists( 'tg_microcopy' ) ? tg_microcopy( 'newsletter_success' ) : __( 'You are on the list. The first invoice is being prepared.', 'touchgrass-core' ),
		] );
	}

	/**
	 * Capture a signup locally without a provider round-trip.
	 *
	 * Used by the test suite and available to other callers; the AJAX
	 * handler above is the normal path.
	 *
	 * @param string $email  Email address.
	 * @param string $source Where the signup came from.
	 * @return int|false Post ID of the tg_subscriber record, false on invalid/duplicate.
	 */
	public static function subscribe_local( $email, $source = 'site' ) {
		$email = sanitize_email( $email );
		if ( ! is_email( $email ) ) {
			return false;
		}
		$slug = 'sub-' . md5( strtolower( $email ) );
		if ( get_page_by_path( $slug, OBJECT, 'tg_subscriber' ) ) {
			return false;
		}
		$post_id = self::record_local( $email, $slug, 'local', '', sanitize_key( $source ) );
		return $post_id ? $post_id : false;
	}

	/**
	 * Subscribe via the Buttondown API.
	 *
	 * @param string $email
	 * @return array [ 'status' => 'subscribed'|'duplicate'|'error', 'id' => string, 'detail' => string ]
	 */
	public static function subscribe_buttondown( $email ) {
		$key = get_option( 'tg_buttondown_api_key', '' );
		if ( ! $key ) {
			return [ 'status' => 'error', 'id' => '', 'detail' => 'no API key configured' ];
		}

		$source = isset( $_POST['source'] ) ? sanitize_key( $_POST['source'] ) : 'site';
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		$response = wp_remote_post( self::API_BASE . '/subscribers', [
			'timeout' => 12,
			'headers' => [
				'Authorization' => 'Token ' . $key,
				'Content-Type'  => 'application/json',
			],
			'body' => wp_json_encode( [
				'email_address' => $email,
				/* No forced tags: Buttondown rejects unknown tags with 403 on
				 * plans without tag support (e.g. free). Merchants on tag-capable
				 * plans can opt in via the tg_newsletter_tags filter. */
				'tags'          => apply_filters( 'tg_newsletter_tags', [] ),
				'metadata'      => [ 'source' => $source ],
				'ip_address'    => $ip,
				'referrer_url'  => home_url( '/' ),
			] ),
		] );

		if ( is_wp_error( $response ) ) {
			return [ 'status' => 'error', 'id' => '', 'detail' => $response->get_error_message() ];
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code >= 200 && $code < 300 ) {
			return [ 'status' => 'subscribed', 'id' => isset( $body['id'] ) ? (string) $body['id'] : '', 'detail' => '' ];
		}

		/* Buttondown reports duplicates as 400 with machine code email_already_exists. */
		$machine_code = is_array( $body ) && isset( $body[0]['code'] ) ? (string) $body[0]['code'] : '';
		if ( 400 === $code && 'email_already_exists' === $machine_code ) {
			return [ 'status' => 'duplicate', 'id' => '', 'detail' => '' ];
		}
		/* Narrow plaintext fallback for the same condition. */
		$detail = is_string( $body ) ? $body : wp_json_encode( $body );
		if ( 400 === $code && is_string( $detail ) && preg_match( '/already (subscribed|exists)/i', $detail ) ) {
			return [ 'status' => 'duplicate', 'id' => '', 'detail' => '' ];
		}

		return [ 'status' => 'error', 'id' => '', 'detail' => "HTTP $code: " . substr( (string) $detail, 0, 300 ) ];
	}

	protected static function record_local( $email, $slug, $provider, $external_id, $source = 'site' ) {
		$post_id = wp_insert_post( [
			'post_type'   => 'tg_subscriber',
			'post_title'  => $email,
			'post_name'   => $slug,
			'post_status' => 'private',
		] );
		if ( ! $post_id || is_wp_error( $post_id ) ) { return 0; }
		update_post_meta( $post_id, '_tg_subscribed_at', current_time( 'mysql' ) );
		update_post_meta( $post_id, '_tg_source', sanitize_key( $source ) );
		update_post_meta( $post_id, '_tg_provider', sanitize_key( $provider ) );
		if ( $external_id ) {
			update_post_meta( $post_id, '_tg_external_id', sanitize_text_field( $external_id ) );
		}
		return $post_id;
	}
}

/**
 * All locally captured subscribers, oldest first.
 *
 * @return array[] Each row: email, subscribed_at, source, provider.
 */
function tg_get_subscribers() {
	$posts = get_posts( [
		'post_type'   => 'tg_subscriber',
		'post_status' => 'private',
		'numberposts' => -1,
		'orderby'     => 'date',
		'order'       => 'ASC',
	] );
	$rows = [];
	foreach ( $posts as $p ) {
		$rows[] = [
			'email'         => $p->post_title,
			'subscribed_at' => get_post_meta( $p->ID, '_tg_subscribed_at', true ),
			'source'        => get_post_meta( $p->ID, '_tg_source', true ),
			'provider'      => get_post_meta( $p->ID, '_tg_provider', true ),
		];
	}
	return $rows;
}

TG_Newsletter::init();
