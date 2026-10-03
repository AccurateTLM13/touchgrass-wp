<?php
/**
 * Touch Grass dashboard: status checklist, settings (demo mode, newsletter),
 * one-click demo import, and the separate explicit demo reset.
 * Administrators only (manage_options).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class TG_Admin {

	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'assets' ] );
		add_action( 'wp_ajax_tg_import_demo', [ __CLASS__, 'ajax_import' ] );
		add_action( 'wp_ajax_tg_reset_products', [ __CLASS__, 'ajax_reset' ] );
	}

	public static function menu() {
		add_menu_page(
			__( 'Touch Grass Setup', 'touchgrass-core' ),
			__( 'Touch Grass', 'touchgrass-core' ),
			'manage_options',
			'touchgrass-core',
			[ __CLASS__, 'page' ],
			'dashicons-palmtree',
			58
		);
	}

	public static function assets( $hook ) {
		if ( $hook !== 'toplevel_page_touchgrass-core' ) { return; }
		wp_enqueue_script(
			'tg-admin',
			TG_CORE_URL . 'assets/js/tg-admin.js',
			[],
			TG_CORE_VERSION,
			true
		);
		wp_localize_script( 'tg-admin', 'tgAdmin', [
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'tg_import_nonce' ),
			'i18n'    => [
				'importing'   => __( 'Importing…', 'touchgrass-core' ),
				'resetting'   => __( 'Resetting…', 'touchgrass-core' ),
				'done'        => __( 'Finished.', 'touchgrass-core' ),
				'failed'      => __( 'Failed. See below.', 'touchgrass-core' ),
				'resetConfirm' => __( 'Reset all demo-managed products to their demo name, description, and prices? Your other products are never touched. This cannot be undone.', 'touchgrass-core' ),
			],
		] );
		wp_add_inline_style( 'common', self::css() );
	}

	protected static function css() {
		return '.tg-check{list-style:none;margin:1em 0;max-width:680px}'
			. '.tg-check li{padding:8px 12px;border:1px solid #dcdcde;background:#fff;margin-bottom:-1px;display:flex;gap:10px;align-items:center}'
			. '.tg-check .ok{color:#008a20;font-weight:700}'
			. '.tg-check .bad{color:#b32d2e;font-weight:700}'
			. '.tg-check .warn{color:#b26a00;font-weight:700}'
			. '.tg-check .detail{margin-left:auto;color:#646970;font-size:12px;text-align:right}'
			. '#tg-import-result{margin-top:1em;max-width:680px}'
			. '.tg-section{background:#fff;border:1px solid #dcdcde;padding:4px 20px 16px;max-width:680px;margin:1.5em 0}';
	}

	/** Each row: [ label, state('ok'|'bad'|'warn'), detail string ]. */
	public static function checklist() {
		$rows = [];
		$woo = tg_core_woo_active();
		$rows[] = [ __( 'WooCommerce active', 'touchgrass-core' ), $woo ? 'ok' : 'bad', $woo ? __( 'Store engine ready.', 'touchgrass-core' ) : __( 'Install and activate WooCommerce first.', 'touchgrass-core' ) ];

		$store_ok = false; $store_detail = __( 'WooCommerce inactive.', 'touchgrass-core' );
		if ( $woo && function_exists( 'wc_get_page_id' ) ) {
			$missing = [];
			$present = [];
			foreach ( [ 'shop' => __( 'Shop', 'touchgrass-core' ), 'cart' => __( 'Cart', 'touchgrass-core' ), 'checkout' => __( 'Checkout', 'touchgrass-core' ), 'myaccount' => __( 'My account', 'touchgrass-core' ) ] as $key => $label ) {
				$page_id = wc_get_page_id( $key );
				if ( $page_id <= 0 || 'publish' !== get_post_status( $page_id ) ) { $missing[] = $label; }
				else { $present[] = $label; }
			}
			$store_ok = empty( $missing );
			$store_detail = $store_ok
				? implode( ', ', $present ) . '.'
				: sprintf( __( 'Missing or unpublished: %s', 'touchgrass-core' ), implode( ', ', $missing ) );
		}
		$rows[] = [ __( 'Store pages exist & published', 'touchgrass-core' ), $store_ok ? 'ok' : 'bad', $store_detail ];

		if ( $woo ) {
			$coming_soon = 'yes' === get_option( 'woocommerce_coming_soon' );
			$rows[] = [
				__( 'Store visibility', 'touchgrass-core' ),
				$coming_soon ? 'warn' : 'ok',
				$coming_soon ? __( '“Coming soon” is ON — the catalog is hidden until you launch it (WooCommerce → Settings). The demo importer never changes this.', 'touchgrass-core' ) : __( 'Live.', 'touchgrass-core' ),
			];
		}

		$found = 0;
		if ( $woo && function_exists( 'wc_get_product_id_by_sku' ) ) {
			foreach ( array_keys( TG_Importer::products() ) as $sku ) {
				if ( wc_get_product_id_by_sku( $sku ) ) { $found++; }
			}
		}
		$rows[] = [ __( 'Demo products', 'touchgrass-core' ), $found === 9 ? 'ok' : ( $found > 0 ? 'warn' : 'bad' ), sprintf( __( '%d of 9', 'touchgrass-core' ), $found ) ];

		$plots = term_exists( 'plots', 'product_cat' );
		$acc = term_exists( 'accessories', 'product_cat' );
		$rows[] = [ __( 'Categories', 'touchgrass-core' ), (bool) $plots && (bool) $acc ? 'ok' : 'bad', ( $plots ? __( 'Plots', 'touchgrass-core' ) : __( 'Plots — missing', 'touchgrass-core' ) ) . ' · ' . ( $acc ? __( 'Accessories', 'touchgrass-core' ) : __( 'Accessories — missing', 'touchgrass-core' ) ) ];

		$coupon = $woo && function_exists( 'wc_get_coupon_id_by_code' ) ? wc_get_coupon_id_by_code( 'GOOUTSIDE' ) : 0;
		$rows[] = [ __( 'Coupon GOOUTSIDE', 'touchgrass-core' ), (bool) $coupon ? 'ok' : 'warn', $coupon ? __( '20% off, active.', 'touchgrass-core' ) : __( 'Not created yet.', 'touchgrass-core' ) ];

		$faqs = (int) wp_count_posts( 'tg_faq' )->publish;
		$rows[] = [ __( 'FAQs', 'touchgrass-core' ), $faqs >= 7 ? 'ok' : ( $faqs > 0 ? 'warn' : 'bad' ), sprintf( __( '%d found', 'touchgrass-core' ), $faqs ) ];

		$tms = (int) wp_count_posts( 'tg_testimonial' )->publish;
		$rows[] = [ __( 'Testimonials', 'touchgrass-core' ), $tms >= 3 ? 'ok' : ( $tms > 0 ? 'warn' : 'bad' ), sprintf( __( '%d found', 'touchgrass-core' ), $tms ) ];

		$menu_ok = has_nav_menu( 'primary' );
		$rows[] = [ __( 'Primary menu assigned', 'touchgrass-core' ), (bool) $menu_ok ? 'ok' : 'warn', $menu_ok ? __( 'Navigation ready.', 'touchgrass-core' ) : __( 'Assign a menu to the Primary location.', 'touchgrass-core' ) ];

		/* Payment gateways: show what's actually enabled; call out demo mode. */
		$gw_state = 'warn'; $gw_detail = __( 'WooCommerce inactive.', 'touchgrass-core' );
		if ( $woo && function_exists( 'WC' ) && WC()->payment_gateways() ) {
			$enabled = [];
			foreach ( WC()->payment_gateways()->payment_gateways() as $gw ) {
				if ( isset( $gw->enabled ) && 'yes' === $gw->enabled ) {
					$enabled[] = $gw->get_title();
				}
			}
			if ( tg_demo_mode() ) {
				$gw_state = 'warn';
				$gw_detail = __( 'DEMO MODE is on — mock payments may be offered. Do not take real orders.', 'touchgrass-core' );
				if ( $enabled ) { $gw_detail .= ' ' . sprintf( __( 'Enabled: %s.', 'touchgrass-core' ), implode( ', ', $enabled ) ); }
			} elseif ( $enabled ) {
				$gw_state = 'ok';
				$gw_detail = sprintf( __( 'Enabled: %s.', 'touchgrass-core' ), implode( ', ', $enabled ) );
			} else {
				$gw_detail = __( 'No payment gateway enabled. Configure one under WooCommerce → Settings → Payments.', 'touchgrass-core' );
			}
		}
		$rows[] = [ __( 'Payment gateways', 'touchgrass-core' ), $gw_state, $gw_detail ];

		$nl = tg_newsletter_configured();
		$rows[] = [ __( 'Newsletter (Buttondown)', 'touchgrass-core' ), $nl ? 'ok' : 'warn', $nl ? __( 'Configured — signups reach your audience.', 'touchgrass-core' ) : __( 'No API key — the signup section is hidden on the site.', 'touchgrass-core' ) ];

		if ( get_option( 'tg_legacy_demo_pay_blocked' ) ) {
			$rows[] = [ __( 'Legacy Demo Pay plugin', 'touchgrass-core' ), 'warn', __( 'Superseded standalone plugin detected and prevented from loading. Deactivate and delete it.', 'touchgrass-core' ) ];
		}

		$imported = get_option( 'tg_demo_imported' );
		if ( $imported ) {
			$rows[] = [ __( 'Demo import', 'touchgrass-core' ), 'ok', sprintf( __( 'Last ran %s.', 'touchgrass-core' ), $imported ) ];
		}

		return $rows;
	}

	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$rows = self::checklist();
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Touch Grass Setup', 'touchgrass-core' ) . '</h1>';

		echo '<h2>' . esc_html__( 'Status', 'touchgrass-core' ) . '</h2>';
		echo '<ul class="tg-check" id="tg-checklist">';
		foreach ( $rows as $row ) {
			echo self::row_html( $row );
		}
		echo '</ul>';

		echo '<div class="tg-section"><h2>' . esc_html__( 'Settings', 'touchgrass-core' ) . '</h2>';
		TG_Settings::form();
		echo '</div>';

		echo '<div class="tg-section"><h2>' . esc_html__( 'Demo content', 'touchgrass-core' ) . '</h2>';
		echo '<p>' . esc_html__( 'Import installs the demo store: categories, images, nine products, coupon, FAQs, testimonials, and navigation. Running it again updates the importer’s own fields instead of duplicating — your product names, descriptions, and prices are never overwritten.', 'touchgrass-core' ) . '</p>';
		echo '<p><button class="button button-primary button-hero" id="tg-import-btn">' . esc_html__( 'Import Demo Content', 'touchgrass-core' ) . '</button></p>';
		echo '<div id="tg-import-result" aria-live="polite"></div>';
		echo '<hr><p><strong>' . esc_html__( 'Reset demo products', 'touchgrass-core' ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Restores the demo name, description, and prices on products the importer manages. Your own products are never touched. This is the only action that overwrites commercial fields.', 'touchgrass-core' ) . '</p>';
		echo '<p><button class="button button-secondary" id="tg-reset-btn">' . esc_html__( 'Reset Demo Products', 'touchgrass-core' ) . '</button></p>';
		echo '<div id="tg-reset-result" aria-live="polite"></div>';
		echo '</div>';

		echo '<div class="tg-section"><h2>' . esc_html__( 'Documentation', 'touchgrass-core' ) . '</h2>';
		echo '<ul>';
		echo '<li>' . esc_html__( 'docs/INSTALL.md — installation, updates, uninstall', 'touchgrass-core' ) . '</li>';
		echo '<li>' . esc_html__( 'docs/PAYMENTS.md — production payment setup and demo mode', 'touchgrass-core' ) . '</li>';
		echo '<li>' . esc_html__( 'docs/NEWSLETTER.md — Buttondown setup, unsubscribe, data retention', 'touchgrass-core' ) . '</li>';
		echo '<li>' . esc_html__( 'docs/CUSTOMIZE.md — every Customizer control and what it changes', 'touchgrass-core' ) . '</li>';
		echo '</ul></div>';

		echo '</div>';
	}

	public static function row_html( $row ) {
		list( $label, $state, $detail ) = $row;
		$mark = 'ok' === $state ? '<span class="ok">✓</span>' : ( 'warn' === $state ? '<span class="warn">!</span>' : '<span class="bad">✗</span>' );
		return '<li>' . $mark . '<span>' . esc_html( $label ) . '</span><span class="detail">' . esc_html( $detail ) . '</span></li>';
	}

	protected static function summarize( $report ) {
		$summary = [];
		foreach ( $report as $step => $t ) {
			if ( ! is_array( $t ) || ! isset( $t['created'] ) ) { continue; }
			$text = sprintf(
				/* translators: 1: created, 2: updated, 3: skipped, 4: failed */
				__( '%1$d created · %2$d updated · %3$d already there · %4$d failed', 'touchgrass-core' ),
				$t['created'], $t['updated'], $t['skipped'], $t['failed']
			);
			if ( isset( $t['note'] ) ) { $text .= ' — ' . $t['note']; }
			$summary[ $step ] = $text;
		}
		return $summary;
	}

	protected static function checklist_html() {
		$rows = [];
		foreach ( self::checklist() as $row ) {
			$rows[] = self::row_html( $row );
		}
		return $rows;
	}

	public static function ajax_import() {
		check_ajax_referer( 'tg_import_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'touchgrass-core' ) ] );
		}
		$report = TG_Importer::run();
		if ( isset( $report['error'] ) ) {
			wp_send_json_error( [ 'message' => $report['error'] ] );
		}
		wp_send_json_success( [ 'summary' => self::summarize( $report ), 'checklist' => self::checklist_html() ] );
	}

	public static function ajax_reset() {
		check_ajax_referer( 'tg_import_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'touchgrass-core' ) ] );
		}
		$report = TG_Importer::reset_products();
		if ( isset( $report['error'] ) ) {
			wp_send_json_error( [ 'message' => $report['error'] ] );
		}
		wp_send_json_success( [ 'summary' => self::summarize( $report ), 'checklist' => self::checklist_html() ] );
	}
}

TG_Admin::init();
