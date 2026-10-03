<?php
/**
 * Touch Grass settings: demo mode switch and newsletter provider credentials.
 * Registered with the Settings API; rendered inside the Touch Grass
 * dashboard (TG_Admin::page) so there is exactly one admin screen.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class TG_Settings {

	public static function init() {
		add_action( 'admin_init', [ __CLASS__, 'register' ] );
	}

	public static function register() {
		register_setting( 'tg_settings', 'tg_demo_mode', [
			'type'              => 'boolean',
			'sanitize_callback' => [ __CLASS__, 'sanitize_bool' ],
			'default'           => false,
		] );
		register_setting( 'tg_settings', 'tg_buttondown_api_key', [
			'type'              => 'string',
			'sanitize_callback' => [ __CLASS__, 'sanitize_key' ],
			'default'           => '',
		] );

		add_settings_section(
			'tg_demo_section',
			__( 'Demo mode', 'touchgrass-core' ),
			[ __CLASS__, 'demo_section_text' ],
			'tg_settings'
		);
		add_settings_field(
			'tg_demo_mode',
			__( 'Enable demo mode', 'touchgrass-core' ),
			[ __CLASS__, 'demo_mode_field' ],
			'tg_settings',
			'tg_demo_section'
		);

		add_settings_section(
			'tg_newsletter_section',
			__( 'Newsletter (Buttondown)', 'touchgrass-core' ),
			[ __CLASS__, 'newsletter_section_text' ],
			'tg_settings'
		);
		add_settings_field(
			'tg_buttondown_api_key',
			__( 'Buttondown API key', 'touchgrass-core' ),
			[ __CLASS__, 'api_key_field' ],
			'tg_settings',
			'tg_newsletter_section'
		);
	}

	public static function sanitize_bool( $value ) {
		return $value ? true : false;
	}

	public static function sanitize_key( $value ) {
		$value = trim( (string) $value );
		/* Buttondown keys are opaque tokens; allow the characters they use. */
		return preg_replace( '/[^A-Za-z0-9\-_\.]/', '', $value );
	}

	public static function demo_section_text() {
		echo '<p>' . esc_html__( 'Demo mode turns this installation into a demo store: the Demo Pay mock gateway becomes available at checkout and demo notices appear on the cart and checkout pages. It is OFF by default — a production store should leave it off and configure real payment gateways under WooCommerce → Settings → Payments.', 'touchgrass-core' ) . '</p>';
		if ( tg_demo_mode() ) {
			echo '<p><strong>' . esc_html__( 'Demo mode is currently ON. Do not take real orders in this state.', 'touchgrass-core' ) . '</strong></p>';
		}
	}

	public static function demo_mode_field() {
		$on = tg_demo_mode();
		echo '<label><input type="checkbox" name="tg_demo_mode" value="1"' . checked( $on, true, false ) . '> '
			. esc_html__( 'This is a demo store (enable mock payments and demo notices)', 'touchgrass-core' )
			. '</label>';
	}

	public static function newsletter_section_text() {
		echo '<p>' . esc_html__( 'Signups are delivered to a Buttondown audience via its API. When no key is saved, the newsletter section is hidden on the site. See docs/NEWSLETTER.md for setup steps.', 'touchgrass-core' ) . '</p>';
		if ( tg_newsletter_configured() ) {
			echo '<p><strong>' . esc_html__( 'A key is saved. New signups reach your Buttondown audience.', 'touchgrass-core' ) . '</strong></p>';
		} else {
			echo '<p>' . esc_html__( 'No key saved — the signup form is currently hidden on the site.', 'touchgrass-core' ) . '</p>';
		}
	}

	public static function api_key_field() {
		$key = get_option( 'tg_buttondown_api_key', '' );
		echo '<input type="password" name="tg_buttondown_api_key" value="' . esc_attr( $key ) . '" class="regular-text" autocomplete="new-password" spellcheck="false"> ';
		echo '<p class="description">' . esc_html__( 'Find it under Buttondown → Settings → API. Stored locally; never displayed again in full.', 'touchgrass-core' ) . '</p>';
	}

	/**
	 * Render the settings form (called from the dashboard page).
	 */
	public static function form() {
		echo '<form method="post" action="options.php">';
		settings_fields( 'tg_settings' );
		do_settings_sections( 'tg_settings' );
		submit_button( __( 'Save settings', 'touchgrass-core' ) );
		echo '</form>';
	}
}

TG_Settings::init();
