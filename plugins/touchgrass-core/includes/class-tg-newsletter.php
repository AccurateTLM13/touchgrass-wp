<?php
/**
 * Newsletter signup: real backend, no fake success.
 * AJAX action: tg_newsletter_subscribe (nonce: tg_newsletter_nonce).
 * Stores each address as a private tg_subscriber post; duplicates rejected.
 *
 * Contract: the theme posts the form to admin-ajax.php with action
 * 'tg_newsletter_subscribe' and the nonce in field 'tg_newsletter_nonce'
 * (nonce action 'tg_newsletter'). No script localization needed.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class TG_Newsletter {

	public static function init() {
		add_action( 'wp_ajax_tg_newsletter_subscribe', [ __CLASS__, 'handle' ] );
		add_action( 'wp_ajax_nopriv_tg_newsletter_subscribe', [ __CLASS__, 'handle' ] );
	}

	public static function handle() {
		check_ajax_referer( 'tg_newsletter', 'tg_newsletter_nonce' );

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			wp_send_json_error( [ 'message' => __( 'That email doesn’t look right. Try again?', 'touchgrass-core' ) ] );
		}

		/* Duplicate check: deterministic hash slug (two distinct emails can
		   sanitize to the same title slug, so don't use the raw email). */
		$slug = 'sub-' . md5( strtolower( $email ) );
		if ( get_page_by_path( $slug, OBJECT, 'tg_subscriber' ) ) {
			wp_send_json_error( [ 'message' => __( 'You’re already on the list. The grass remembers.', 'touchgrass-core' ) ] );
		}

		$post_id = wp_insert_post( [
			'post_type'   => 'tg_subscriber',
			'post_title'  => $email,
			'post_name'   => $slug,
			'post_status' => 'private',
		] );

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			wp_send_json_error( [ 'message' => __( 'Something wilted on our end. Please try again.', 'touchgrass-core' ) ] );
		}

		$source = isset( $_POST['source'] ) ? sanitize_key( $_POST['source'] ) : 'footer';
		update_post_meta( $post_id, '_tg_subscribed_at', current_time( 'mysql' ) );
		update_post_meta( $post_id, '_tg_source', $source );

		wp_send_json_success( [
			'message' => __( 'You are on the list. The first invoice is being prepared.', 'touchgrass-core' ),
		] );
	}
}

TG_Newsletter::init();
