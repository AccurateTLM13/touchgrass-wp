<?php
/**
 * Settings → Touch Grass dashboard: status checklist + one-click demo import.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class TG_Admin {

	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'assets' ] );
		add_action( 'wp_ajax_tg_import_demo', [ __CLASS__, 'ajax_import' ] );
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
				'importing' => __( 'Importing…', 'touchgrass-core' ),
				'done'      => __( 'Import finished.', 'touchgrass-core' ),
				'failed'    => __( 'Import failed. See below.', 'touchgrass-core' ),
			],
		] );
		wp_add_inline_style( 'common', self::css() );
	}

	protected static function css() {
		return '.tg-check{list-style:none;margin:1em 0;max-width:640px}'
			. '.tg-check li{padding:8px 12px;border:1px solid #dcdcde;background:#fff;margin-bottom:-1px;display:flex;gap:10px;align-items:center}'
			. '.tg-check .ok{color:#008a20;font-weight:700}'
			. '.tg-check .bad{color:#b32d2e;font-weight:700}'
			. '.tg-check .detail{margin-left:auto;color:#646970;font-size:12px}'
			. '#tg-import-result{margin-top:1em;max-width:640px}';
	}

	/** Each row: [ label, ok(bool), detail string ]. */
	public static function checklist() {
		$rows = [];
		$woo = tg_core_woo_active();
		$rows[] = [ __( 'WooCommerce active', 'touchgrass-core' ), $woo, $woo ? __( 'Store engine ready.', 'touchgrass-core' ) : __( 'Install and activate WooCommerce first.', 'touchgrass-core' ) ];

		$store_ok = false; $store_detail = __( 'WooCommerce inactive.', 'touchgrass-core' );
		if ( $woo && function_exists( 'wc_get_page_id' ) ) {
			$missing = [];
			foreach ( [ 'shop' => __( 'Shop', 'touchgrass-core' ), 'cart' => __( 'Cart', 'touchgrass-core' ), 'checkout' => __( 'Checkout', 'touchgrass-core' ), 'myaccount' => __( 'My account', 'touchgrass-core' ) ] as $key => $label ) {
				if ( wc_get_page_id( $key ) <= 0 ) { $missing[] = $label; }
			}
			$store_ok = empty( $missing );
			$store_detail = $store_ok ? __( 'Shop, Cart, Checkout, My account.', 'touchgrass-core' ) : sprintf( __( 'Missing: %s', 'touchgrass-core' ), implode( ', ', $missing ) );
		}
		$rows[] = [ __( 'Store pages', 'touchgrass-core' ), $store_ok, $store_detail ];

		$found = 0;
		if ( $woo && function_exists( 'wc_get_product_id_by_sku' ) ) {
			foreach ( array_keys( TG_Importer::products() ) as $sku ) {
				if ( wc_get_product_id_by_sku( $sku ) ) { $found++; }
			}
		}
		$rows[] = [ __( 'Demo products', 'touchgrass-core' ), $found === 9, sprintf( __( '%d of 9', 'touchgrass-core' ), $found ) ];

		$plots = term_exists( 'plots', 'product_cat' );
		$acc = term_exists( 'accessories', 'product_cat' );
		$rows[] = [ __( 'Categories', 'touchgrass-core' ), (bool) $plots && (bool) $acc, ( $plots ? __( 'Plots', 'touchgrass-core' ) : __( 'Plots — missing', 'touchgrass-core' ) ) . ' · ' . ( $acc ? __( 'Accessories', 'touchgrass-core' ) : __( 'Accessories — missing', 'touchgrass-core' ) ) ];

		$coupon = $woo && function_exists( 'wc_get_coupon_id_by_code' ) ? wc_get_coupon_id_by_code( 'GOOUTSIDE' ) : 0;
		$rows[] = [ __( 'Coupon GOOUTSIDE', 'touchgrass-core' ), (bool) $coupon, $coupon ? __( '20% off, active.', 'touchgrass-core' ) : __( 'Not created yet.', 'touchgrass-core' ) ];

		$faqs = (int) wp_count_posts( 'tg_faq' )->publish;
		$rows[] = [ __( 'FAQs', 'touchgrass-core' ), $faqs >= 7, sprintf( __( '%d found', 'touchgrass-core' ), $faqs ) ];

		$tms = (int) wp_count_posts( 'tg_testimonial' )->publish;
		$rows[] = [ __( 'Testimonials', 'touchgrass-core' ), $tms >= 3, sprintf( __( '%d found', 'touchgrass-core' ), $tms ) ];

		$menu_ok = has_nav_menu( 'primary' );
		$rows[] = [ __( 'Primary menu assigned', 'touchgrass-core' ), (bool) $menu_ok, $menu_ok ? __( 'Navigation ready.', 'touchgrass-core' ) : __( 'Assign a menu to the Primary location.', 'touchgrass-core' ) ];

		$gw_ok = false; $gw_detail = __( 'WooCommerce inactive.', 'touchgrass-core' );
		if ( $woo && function_exists( 'WC' ) && WC()->payment_gateways() ) {
			$gateways = WC()->payment_gateways()->payment_gateways();
			if ( isset( $gateways['touchgrass_demo'] ) && $gateways['touchgrass_demo']->enabled === 'yes' ) {
				$gw_ok = true;
				$gw_detail = __( 'Demo Pay is on.', 'touchgrass-core' );
			} else {
				$gw_detail = __( 'Enable Demo Pay under WooCommerce → Settings → Payments.', 'touchgrass-core' );
			}
		}
		$rows[] = [ __( 'Demo Pay enabled', 'touchgrass-core' ), $gw_ok, $gw_detail ];

		return $rows;
	}

	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$rows = self::checklist();
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Touch Grass Setup', 'touchgrass-core' ) . '</h1>';
		echo '<p>' . esc_html__( 'One click installs the full demo store: categories, images, nine products, coupon, FAQs, testimonials, and navigation. Running it again updates instead of duplicating.', 'touchgrass-core' ) . '</p>';
		echo '<ul class="tg-check" id="tg-checklist">';
		foreach ( $rows as $row ) {
			echo self::row_html( $row );
		}
		echo '</ul>';
		echo '<p><button class="button button-primary button-hero" id="tg-import-btn">' . esc_html__( 'Import Demo Content', 'touchgrass-core' ) . '</button></p>';
		echo '<div id="tg-import-result" aria-live="polite"></div>';
		echo '</div>';
	}

	public static function row_html( $row ) {
		list( $label, $ok, $detail ) = $row;
		$mark = $ok ? '<span class="ok">✓</span>' : '<span class="bad">✗</span>';
		return '<li>' . $mark . '<span>' . esc_html( $label ) . '</span><span class="detail">' . esc_html( $detail ) . '</span></li>';
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
		$summary = [];
		foreach ( $report as $step => $t ) {
			if ( ! is_array( $t ) || ! isset( $t['created'] ) ) { continue; }
			$summary[ $step ] = sprintf(
				/* translators: 1: created, 2: updated, 3: skipped */
				__( '%1$d created · %2$d updated · %3$d already there', 'touchgrass-core' ),
				$t['created'], $t['updated'], $t['skipped']
			);
		}
		$rows = [];
		foreach ( self::checklist() as $row ) {
			$rows[] = self::row_html( $row );
		}
		wp_send_json_success( [ 'summary' => $summary, 'checklist' => $rows ] );
	}
}

TG_Admin::init();
