<?php
/**
 * Demo-content importer for the Touch Grass store.
 *
 * Design principles (production quality):
 *
 * - Reruns NEVER duplicate: every object is looked up before it is created.
 * - Reruns NEVER overwrite merchant edits to commercial fields. Products with
 *   a demo SKU are flagged _tg_demo_managed (adopted on rerun if an older
 *   version imported them); on rerun only the importer's "voice" fields
 *   (tagline, badge, image, categories) refresh — name, description, prices,
 *   and sale status are left exactly as the merchant left them. A separate,
 *   explicit reset action restores demo values for managed products.
 * - Bundled images are copied to temp files before sideloading, so an import
 *   can never consume or move the plugin's own assets.
 * - Failures are counted and reported per step (created / updated / skipped /
 *   failed) instead of silently dropped.
 * - The import never changes store visibility (woocommerce_coming_soon) and
 *   never touches payment gateway configuration.
 * - Imported FAQs/testimonials are flagged _tg_demo_content so the dashboard
 *   can identify them as demo material.
 *
 * Pure PHP — no WP-CLI, no SSH. Runs from the admin dashboard, administrators
 * only (manage_options capability, verified in the AJAX handler).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class TG_Importer {

	/** SKU => [ name, price, sale, cats, badge, tagline, image, description ] */
	public static function products() {
		return [
			'TG-DAILY' => [
				'name'        => 'The Daily Driver',
				'price'       => 29, 'sale' => 0,
				'cats'        => [ 'plots' ], 'badge' => 'bestseller',
				'tagline'     => 'Our flagship rectangle of grass.',
				'image'       => 'daily',
				'description' => 'The plot that started it all. A generous 8×10 inches of premium fescue, grown in our greenhouse and shipped in a keepsake tray. For the desk, the shelf, or wherever you pretend to take breaks.',
			],
			'TG-COMMUTER' => [
				'name'        => 'The Commuter',
				'price'       => 24, 'sale' => 0,
				'cats'        => [ 'plots' ], 'badge' => '',
				'tagline'     => 'Grass that travels better than you do.',
				'image'       => 'commuter',
				'description' => 'A compact 6×6 plot in a shatterproof travel case. Fits in a tote bag, a backpack, or the cupholder of poor decisions. Same grass, smaller commitment.',
			],
			'TG-STANDUP' => [
				'name'        => 'The Standup',
				'price'       => 34, 'sale' => 0,
				'cats'        => [ 'plots' ], 'badge' => '',
				'tagline'     => 'Makes meetings 40% calmer.',
				'image'       => 'standup',
				'description' => 'A wide, low plot designed for conference tables. Pass it around during standup: whoever holds the grass holds the floor. HR has questions; we have no answers.',
			],
			'TG-PROMAX' => [
				'name'        => 'The Pro Max',
				'price'       => 74, 'sale' => 59,
				'cats'        => [ 'plots' ], 'badge' => 'lowstock',
				'tagline'     => 'Twice the grass. Half the shame.',
				'image'       => 'promax',
				'description' => 'Our largest plot: a full 12×16 inches of championship-grade turf in a weighted walnut tray. For executives, streamers, and anyone whose monitor deserves a better neighbor. Low stock — the greenhouse can only grow so much glory.',
			],
			'TG-PAIR' => [
				'name'        => 'Pair Programmer',
				'price'       => 44, 'sale' => 0,
				'cats'        => [ 'plots' ], 'badge' => '',
				'tagline'     => 'Two plots. One for each of you.',
				'image'       => 'pair',
				'description' => 'A divided tray with two independent plots — one for you, one for your pair. Water on the same schedule. Grow at the same pace. Argue about tabs versus spaces in front of it.',
			],
			'TG-STARTER' => [
				'name'        => 'Seedling Starter Kit',
				'price'       => 19, 'sale' => 0,
				'cats'        => [ 'plots' ], 'badge' => '',
				'tagline'     => 'Grow your own. Eventually.',
				'image'       => 'starter',
				'description' => 'Everything you need to grow grass from seed: heirloom seed packet, soil pods, mister bottle, and instructions written for people who have killed succulents. Germination in 7–10 days. Pride in 14.',
			],
			'TG-NIGHT' => [
				'name'        => 'The Night Shift',
				'price'       => 39, 'sale' => 0,
				'cats'        => [ 'plots' ], 'badge' => 'staffpick',
				'tagline'     => 'Thrives under monitor glow.',
				'image'       => 'night',
				'description' => 'A shade-tolerant cultivar selected for low-light apartments and 2 a.m. deploy windows. Grows happily under nothing but monitor glow and regret. Staff pick — we keep one in the office and it has seen things.',
			],
			'TG-GNOME' => [
				'name'        => 'The Tiny Gnome',
				'price'       => 12, 'sale' => 0,
				'cats'        => [ 'accessories' ], 'badge' => '',
				'tagline'     => 'Supervises your grass.',
				'image'       => 'gnome',
				'description' => 'A hand-painted ceramic gnome, two inches of pure authority. Place him in any plot to increase yields (unverified) and morale (undeniable). Not grass. Still essential.',
			],
			'TG-MISTER' => [
				'name'        => 'The Mister',
				'price'       => 16, 'sale' => 0,
				'cats'        => [ 'accessories' ], 'badge' => '',
				'tagline'     => 'For the daily ritual.',
				'image'       => 'mister',
				'description' => 'A brass mister bottle with a fine, even spray. The grass prefers a light mist every two to three days. You will prefer the ritual. Refillable, endlessly.',
			],
		];
	}

	public static function faqs() {
		return [
			[ 'Is the grass real?', 'Certified 100% real grass. *Grass.' ],
			[ 'Is this a joke?', 'Going outside is free. This is the paid alternative.' ],
			[ 'What if my grass dies?', 'Then it lived a short, beautiful, indoor life. 30-day replacement, no interrogation.' ],
			[ 'How often do I water it?', 'A light mist every two to three days. The Mister exists for exactly this ritual.' ],
			[ 'Can I eat it?', 'You can do anything once.' ],
			[ 'Do you ship internationally?', 'Currently the US only. The grass is patriotic, but we\'re working on it.' ],
			[ 'What\'s the difference between the plots?', 'Size, species, and attitude. The Daily Driver is the all-rounder; the Night Shift tolerates your cave.' ],
			[ 'Why does checkout say "Invoice"?', 'Because we\'re not judging. We\'re invoicing.' ],
		];
	}

	public static function testimonials() {
		return [
			[ 'Devon K.', 'Backend dev · The Daily Driver', 5, 'I bought it as a joke for our hackathon team. It is now the most important object in the office. Our standups are 40% calmer.' ],
			[ 'Priya S.', 'ML engineer · The Pro Max', 5, 'My therapist asked what changed. I said grass. She wrote it down.' ],
			[ 'Marcus T.', 'Frontend dev · The Night Shift', 5, 'It sits next to my monitor. I have named it. We are close.' ],
		];
	}

	/**
	 * SKUs that are grass plots (as opposed to accessories). Plots get a
	 * harvest batch and cross-sells; accessories don't.
	 *
	 * @return string[]
	 */
	public static function plot_skus() {
		return [ 'TG-DAILY', 'TG-COMMUTER', 'TG-STANDUP', 'TG-PROMAX', 'TG-PAIR', 'TG-STARTER', 'TG-NIGHT' ];
	}

	/**
	 * Per-product "Deed" spec overrides. Fields not listed fall back to the
	 * house defaults in TG_Product_Meta::deed_defaults().
	 *
	 * @return array SKU => [ field => value ]
	 */
	public static function deed_overrides() {
		return [
			'TG-DAILY'    => [ 'provenance' => __( 'Plot 7, Surrey Grassworks', 'touchgrass-core' ) ],
			'TG-COMMUTER' => [ 'provenance' => __( 'Plot 12, Surrey Grassworks — travel division', 'touchgrass-core' ) ],
			'TG-STANDUP'  => [ 'provenance' => __( 'Plot 3, Surrey Grassworks', 'touchgrass-core' ) ],
			'TG-PROMAX'   => [ 'provenance' => __( 'Plot 1, Surrey Grassworks — championship row', 'touchgrass-core' ) ],
			'TG-PAIR'     => [ 'provenance' => __( 'Plots 9 & 10, Surrey Grassworks', 'touchgrass-core' ) ],
			'TG-STARTER'  => [ 'provenance' => __( 'Seed vault, Surrey Grassworks', 'touchgrass-core' ) ],
			'TG-NIGHT'    => [ 'provenance' => __( 'Plot 13, Surrey Grassworks — low-light ward', 'touchgrass-core' ) ],
			'TG-GNOME'    => [
				'provenance' => __( 'Kiln 2, Stoke Ceramics', 'touchgrass-core' ),
				'blades'     => __( '0 — ceramic', 'touchgrass-core' ),
				'sunlight'   => __( 'Thrives in darkness. Like your hobbies.', 'touchgrass-core' ),
				'watering'   => __( 'Never. He judges.', 'touchgrass-core' ),
				'warranty'   => __( 'Lifetime. He is eternal.', 'touchgrass-core' ),
			],
			'TG-MISTER'   => [
				'provenance' => __( 'Brassworks, Birmingham', 'touchgrass-core' ),
				'blades'     => __( '0 — brass', 'touchgrass-core' ),
				'sunlight'   => __( 'Not applicable.', 'touchgrass-core' ),
				'watering'   => __( 'Contains water. Do not water the mister.', 'touchgrass-core' ),
				'warranty'   => __( 'Refillable, endlessly.', 'touchgrass-core' ),
			],
		];
	}

	/**
	 * Per-product "Deed" institutional hierarchy overrides. Plot numbers are
	 * deterministic per SKU — the registry does not improvise.
	 *
	 * @return array SKU => [ field => value ]
	 */
	public static function deed_hierarchy_overrides() {
		return [
			'TG-DAILY'    => [ 'plot' => __( 'Plot 184-C', 'touchgrass-core' ) ],
			'TG-COMMUTER' => [ 'plot' => __( 'Plot 212-A', 'touchgrass-core' ) ],
			'TG-STANDUP'  => [ 'plot' => __( 'Plot 96-F', 'touchgrass-core' ) ],
			'TG-PROMAX'   => [
				'plot'           => __( 'Plot 001-A', 'touchgrass-core' ),
				'classification' => __( 'Fescue Classification: Championship Row Grade', 'touchgrass-core' ),
			],
			'TG-PAIR'     => [ 'plot' => __( 'Plots 9 & 10', 'touchgrass-core' ) ],
			'TG-STARTER'  => [
				'plot'           => __( 'Seed Vault 7', 'touchgrass-core' ),
				'classification' => __( 'Seed Classification: Nascent Grade', 'touchgrass-core' ),
			],
			'TG-NIGHT'    => [
				'division'       => __( 'Subterranean Division', 'touchgrass-core' ),
				'plot'           => __( 'Plot 13-D', 'touchgrass-core' ),
				'classification' => __( 'Shade-Tolerance Classification: Cave Dweller Grade', 'touchgrass-core' ),
			],
			'TG-MISTER'   => [
				'division'       => __( 'Hydration Apparatus Division', 'touchgrass-core' ),
				'harvest'        => __( 'Lot 12', 'touchgrass-core' ),
				'plot'           => __( 'Unit 12-B', 'touchgrass-core' ),
				'classification' => __( 'Brass Classification: Utilitarian', 'touchgrass-core' ),
			],
			'TG-GNOME'    => [
				'division'       => __( 'Ornamental Division', 'touchgrass-core' ),
				'harvest'        => __( 'Lot 3', 'touchgrass-core' ),
				'plot'           => __( 'Unit 3-C', 'touchgrass-core' ),
				'classification' => __( 'Ceramic Classification: Ornamental', 'touchgrass-core' ),
			],
		];
	}

	/**
	 * Run the full import. Returns step => [ created, updated, skipped, failed ].
	 */
	public static function run() {
		if ( ! tg_core_woo_active() ) {
			return [ 'error' => __( 'WooCommerce is not active. Activate it, then run the import.', 'touchgrass-core' ) ];
		}
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$report = [];
		$report['categories']   = self::import_categories();
		$report['media']        = self::import_media();
		$report['products']     = self::import_products( $report['media']['map'] );
		$report['coupon']       = self::import_coupon();
		$report['faqs']         = self::import_faqs();
		$report['testimonials'] = self::import_testimonials();
		$report['menu']         = self::import_menu();
		$report['stats']        = self::import_demo_stats();

		/* Store visibility is reported, never changed: importing demo content
		 * must not publish the store or touch payment configuration. */
		$report['store_visibility'] = [
			'created' => 0,
			'updated' => 0,
			'skipped' => 0,
			'failed'  => 0,
			'note'    => 'yes' === get_option( 'woocommerce_coming_soon' )
				? __( 'Store is in “coming soon” mode — launch it from WooCommerce → Settings when ready.', 'touchgrass-core' )
				: __( 'Store is live.', 'touchgrass-core' ),
		];

		update_option( 'tg_demo_imported', current_time( 'mysql' ) );

		return $report;
	}

	/**
	 * Explicit reset: restore demo name/description/prices for products the
	 * importer manages. Merchant-created products are never touched.
	 * This is the ONLY action that overwrites commercial fields — it is
	 * separate from import and requires confirmation in the dashboard.
	 *
	 * @return array step => counts
	 */
	public static function reset_products() {
		if ( ! tg_core_woo_active() ) {
			return [ 'error' => __( 'WooCommerce is not active.', 'touchgrass-core' ) ];
		}
		$t = self::tally();
		foreach ( self::products() as $sku => $data ) {
			$product_id = function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product_id_by_sku( $sku ) : 0;
			if ( ! $product_id ) { $t['skipped']++; continue; }
			if ( ! get_post_meta( $product_id, '_tg_demo_managed', true ) ) { $t['skipped']++; continue; }
			$product = wc_get_product( $product_id );
			if ( ! $product ) { $t['failed']++; continue; }
			$product->set_name( $data['name'] );
			$product->set_description( $data['description'] );
			$product->set_short_description( $data['tagline'] );
			$product->set_regular_price( (string) $data['price'] );
			$product->set_sale_price( $data['sale'] ? (string) $data['sale'] : '' );
			$product->set_price( $data['sale'] ? (string) $data['sale'] : (string) $data['price'] );
			$product->update_meta_data( '_tg_tagline', $data['tagline'] );
			$product->update_meta_data( '_tg_badge', $data['badge'] );
			$product->save();
			$t['updated']++;
		}
		return [ 'products_reset' => $t ];
	}

	protected static function tally() {
		return [ 'created' => 0, 'updated' => 0, 'skipped' => 0, 'deleted' => 0, 'failed' => 0 ];
	}

	/* ---------- categories ---------- */

	protected static function import_categories() {
		$t = self::tally();
		$cats = [
			'plots'       => __( 'Plots', 'touchgrass-core' ),
			'accessories' => __( 'Accessories', 'touchgrass-core' ),
		];
		foreach ( $cats as $slug => $name ) {
			if ( term_exists( $slug, 'product_cat' ) ) {
				$t['skipped']++;
				continue;
			}
			$result = wp_insert_term( $name, 'product_cat', [ 'slug' => $slug ] );
			if ( is_wp_error( $result ) ) { $t['failed']++; } else { $t['created']++; }
		}
		return $t;
	}

	/**
	 * Demo proof stats for the homepage "reviews" section.
	 *
	 * The theme ships with empty stat defaults (no fictional claims on a fresh
	 * install). The demo importer fills them in as clearly-marked demo
	 * material — but only when the merchant hasn't customized them, so reruns
	 * preserve merchant edits.
	 *
	 * @return array
	 */
	protected static function import_demo_stats() {
		$t = self::tally();
		$demo_stats = [
			'tg_proof_title'  => __( '8,600 indoor humans.<br>Zero walks taken.', 'touchgrass-core' ),
			'tg_stat_1_value' => __( '8,600+', 'touchgrass-core' ),
			'tg_stat_1_label' => __( 'verified reviews', 'touchgrass-core' ),
			'tg_stat_2_value' => __( '4.9', 'touchgrass-core' ),
			'tg_stat_2_label' => __( 'average rating', 'touchgrass-core' ),
		];
		foreach ( $demo_stats as $mod => $value ) {
			if ( '' !== trim( (string) get_theme_mod( $mod, '' ) ) ) {
				$t['skipped']++;
				continue;
			}
			set_theme_mod( $mod, $value );
			$t['created']++;
		}
		if ( $t['created'] > 0 ) {
			/* Flag so the dashboard can identify these as demo material. */
			update_option( 'tg_demo_stats_set', current_time( 'mysql' ) );
		}
		return $t;
	}

	/* ---------- media ---------- */

	protected static function import_media() {
		$t = self::tally();
		$map = [];
		$dir = TG_CORE_PATH . 'assets/demo/';
		$files = glob( $dir . '*.webp' );
		if ( ! $files ) {
			$t['failed']++;
			$t['map'] = $map;
			return $t;
		}
		foreach ( $files as $file ) {
			$slug = pathinfo( $file, PATHINFO_FILENAME );
			$existing = get_page_by_path( $slug, OBJECT, 'attachment' );
			if ( $existing ) {
				$map[ $slug ] = (int) $existing->ID;
				$t['skipped']++;
				continue;
			}
			/* Copy to a temp file first: media_handle_sideload() MOVES its
			 * tmp_name, so handing it the plugin's own asset would consume
			 * the bundled image on the first import. */
			$tmp = wp_tempnam( basename( $file ) );
			if ( ! $tmp || ! @copy( $file, $tmp ) ) {
				$t['failed']++;
				continue;
			}
			$file_array = [
				'name'     => basename( $file ),
				'type'     => 'image/webp',
				'tmp_name' => $tmp,
				'error'    => 0,
				'size'     => filesize( $tmp ),
			];
			$att_id = media_handle_sideload( $file_array, 0 );
			if ( is_wp_error( $att_id ) ) {
				$t['failed']++;
				@unlink( $tmp );
				continue;
			}
			/* Pin the slug so reruns find it. */
			wp_update_post( [ 'ID' => $att_id, 'post_name' => $slug ] );
			$map[ $slug ] = (int) $att_id;
			$t['created']++;
		}
		$t['map'] = $map;
		return $t;
	}

	/* ---------- products ---------- */

	protected static function import_products( $media_map ) {
		$t = self::tally();
		$cat_ids = [];
		foreach ( [ 'plots', 'accessories' ] as $slug ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			if ( $term ) { $cat_ids[ $slug ] = (int) $term->term_id; }
		}
		$deed_overrides = self::deed_overrides();
		$hier_overrides  = self::deed_hierarchy_overrides();
		$plot_skus      = self::plot_skus();
		foreach ( self::products() as $sku => $data ) {
			$product_id = function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product_id_by_sku( $sku ) : 0;
			$ids = [];
			foreach ( $data['cats'] as $slug ) {
				if ( isset( $cat_ids[ $slug ] ) ) { $ids[] = $cat_ids[ $slug ]; }
			}
			/* Voice fields: refreshed on every run, never commercial. */
			$voice = function ( $product ) use ( $data, $sku, $deed_overrides, $hier_overrides, $plot_skus ) {
				$product->update_meta_data( '_tg_tagline', $data['tagline'] );
				$product->update_meta_data( '_tg_badge', $data['badge'] );
				if ( in_array( $sku, $plot_skus, true ) ) {
					$product->update_meta_data( '_tg_batch', __( 'Batch No. 7', 'touchgrass-core' ) );
				}
				if ( isset( $deed_overrides[ $sku ] ) ) {
					foreach ( $deed_overrides[ $sku ] as $field => $value ) {
						$product->update_meta_data( '_tg_deed_' . $field, $value );
					}
				}
				if ( isset( $hier_overrides[ $sku ] ) ) {
					foreach ( $hier_overrides[ $sku ] as $field => $value ) {
						$product->update_meta_data( '_tg_deed_' . $field, $value );
					}
				}
			};
			if ( $product_id ) {
				/* Existing product: adopt it into demo management if it
				 * isn't flagged yet (e.g. imported by an older version),
				 * then refresh the importer's "voice" fields. Commercial
				 * fields (name, description, prices) are NEVER touched
				 * here — a merchant's edits survive reruns. Only the
				 * separate, explicit reset action restores demo values. */
				$product = wc_get_product( $product_id );
				if ( ! $product ) { $t['failed']++; continue; }
				if ( ! get_post_meta( $product_id, '_tg_demo_managed', true ) ) {
					update_post_meta( $product_id, '_tg_demo_managed', '1' );
				}
				$voice( $product );
				if ( $ids ) { $product->set_category_ids( $ids ); }
				if ( isset( $media_map[ $data['image'] ] ) && ! $product->get_image_id() ) {
					$product->set_image_id( $media_map[ $data['image'] ] );
				}
				$product->save();
				$t['updated']++;
			} else {
				$product = new WC_Product_Simple();
				$product->set_sku( $sku );
				$product->set_status( 'publish' );
				$product->set_catalog_visibility( 'visible' );
				$product->set_name( $data['name'] );
				$product->set_description( $data['description'] );
				$product->set_short_description( $data['tagline'] );
				$product->set_regular_price( (string) $data['price'] );
				$product->set_sale_price( $data['sale'] ? (string) $data['sale'] : '' );
				$product->set_price( $data['sale'] ? (string) $data['sale'] : (string) $data['price'] );
				if ( $ids ) { $product->set_category_ids( $ids ); }
				if ( isset( $media_map[ $data['image'] ] ) ) {
					$product->set_image_id( $media_map[ $data['image'] ] );
				}
				$voice( $product );
				$product->update_meta_data( '_tg_demo_managed', '1' );
				$id = $product->save();
				if ( ! $id ) { $t['failed']++; } else { $t['created']++; }
			}
		}
		/* Cross-sells: The Mister + Tiny Gnome on every plot ("Complete the
		 * Ritual"). Set on create; on rerun only when the merchant hasn't
		 * customized cross-sells yet. */
		$mister_id = function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product_id_by_sku( 'TG-MISTER' ) : 0;
		$gnome_id  = function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product_id_by_sku( 'TG-GNOME' ) : 0;
		$xs = array_values( array_filter( [ (int) $mister_id, (int) $gnome_id ] ) );
		if ( $xs ) {
			foreach ( self::plot_skus() as $sku ) {
				$pid = function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product_id_by_sku( $sku ) : 0;
				if ( ! $pid ) { continue; }
				$product = wc_get_product( $pid );
				if ( ! $product ) { continue; }
				if ( ! empty( $product->get_cross_sell_ids() ) ) { continue; }
				$product->set_cross_sell_ids( array_diff( $xs, [ $pid ] ) );
				$product->save();
			}
		}
		return $t;
	}

	/* ---------- coupon ---------- */

	protected static function import_coupon() {
		$t = self::tally();
		$code = 'GOOUTSIDE';
		$existing = function_exists( 'wc_get_coupon_id_by_code' ) ? wc_get_coupon_id_by_code( $code ) : 0;
		if ( $existing ) {
			$t['skipped']++;
			return $t;
		}
		$coupon = new WC_Coupon();
		$coupon->set_code( $code );
		$coupon->set_discount_type( 'percent' );
		$coupon->set_amount( 20 );
		$coupon->set_description( __( 'Fresh Cut Friday — 20% abatement on all plots.', 'touchgrass-core' ) );
		$id = $coupon->save();
		if ( ! $id ) { $t['failed']++; } else { $t['created']++; }
		return $t;
	}

	/* ---------- FAQs ---------- */

	/**
	 * FAQs retired by the 7 → 8 migration (v2.2.0). Deleted on import only
	 * when both title AND content still match the old demo copy — a merchant
	 * who rewrote one keeps their version.
	 *
	 * @return array [ title, content ][]
	 */
	public static function retired_faqs() {
		return [
			[ 'Will it survive my apartment?', 'If you survive your apartment, the grass will. It asks less of you than your houseplants did.' ],
			[ 'How fast is shipping?', 'Orders leave the greenhouse within 48 hours. The grass travels better than you do.' ],
			[ "Isn't going outside free?", "Yes. And yet here you are. We're not judging. We're invoicing." ],
		];
	}

	protected static function import_faqs() {
		$t = self::tally();
		$order = 0;
		$live_slugs = [];
		foreach ( self::faqs() as $faq ) {
			$order += 10;
			$slug = sanitize_title( $faq[0] );
			$live_slugs[] = $slug;
			$existing = get_page_by_path( $slug, OBJECT, 'tg_faq' );
			if ( $existing ) {
				/* Voice field: refresh answer + order on rerun, like the
				 * product taglines. */
				wp_update_post( [
					'ID'           => $existing->ID,
					'post_content' => $faq[1],
				] );
				update_post_meta( $existing->ID, '_tg_faq_order', $order );
				$t['updated']++;
				continue;
			}
			$post_id = wp_insert_post( [
				'post_type'    => 'tg_faq',
				'post_title'   => $faq[0],
				'post_name'    => $slug,
				'post_content' => $faq[1],
				'post_status'  => 'publish',
			] );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_tg_faq_order', $order );
				update_post_meta( $post_id, '_tg_demo_content', '1' );
				$t['created']++;
			} else {
				$t['failed']++;
			}
		}
		/* Remove stale demo FAQs (e.g. retired questions from an older demo
		 * set). Only demo-flagged posts are eligible; merchant-created FAQs
		 * are never touched. */
		$stale = get_posts( [
			'post_type'   => 'tg_faq',
			'post_status' => 'any',
			'numberposts' => -1,
			'meta_key'    => '_tg_demo_content',
			'meta_value'  => '1',
			'fields'      => 'ids',
		] );
		foreach ( $stale as $post_id ) {
			$post = get_post( $post_id );
			if ( $post && ! in_array( $post->post_name, $live_slugs, true ) ) {
				wp_delete_post( $post_id, true );
				$t['deleted']++;
			}
		}
		/* One-time retirement of pre-flag demo FAQs: match on exact old
		 * title + content so merchant rewrites are never removed. */
		foreach ( self::retired_faqs() as $retired ) {
			$slug = sanitize_title( $retired[0] );
			if ( in_array( $slug, $live_slugs, true ) ) {
				continue;
			}
			$post = get_page_by_path( $slug, OBJECT, 'tg_faq' );
			if ( $post && trim( $post->post_content ) === $retired[1]
				&& '' === get_post_meta( $post->ID, '_tg_demo_content', true ) ) {
				/* Unflagged but byte-identical to old demo copy: safe to retire. */
				wp_delete_post( $post->ID, true );
				$t['deleted']++;
			}
		}
		return $t;
	}

	/* ---------- testimonials ---------- */

	protected static function import_testimonials() {
		$t = self::tally();
		foreach ( self::testimonials() as $tm ) {
			$slug = sanitize_title( $tm[0] );
			$existing = get_page_by_path( $slug, OBJECT, 'tg_testimonial' );
			if ( $existing ) {
				$t['skipped']++;
				continue;
			}
			$post_id = wp_insert_post( [
				'post_type'    => 'tg_testimonial',
				'post_title'   => $tm[0],
				'post_name'    => $slug,
				'post_content' => $tm[3],
				'post_status'  => 'publish',
			] );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_tg_role', $tm[1] );
				update_post_meta( $post_id, '_tg_rating', $tm[2] );
				update_post_meta( $post_id, '_tg_demo_content', '1' );
				$t['created']++;
			} else {
				$t['failed']++;
			}
		}
		return $t;
	}

	/* ---------- menu ---------- */

	protected static function import_menu() {
		$t = self::tally();
		$menu = wp_get_nav_menu_object( 'Primary' );
		if ( ! $menu ) {
			$menu_id = wp_create_nav_menu( 'Primary' );
			if ( is_wp_error( $menu_id ) ) { $t['failed']++; return $t; }
		} else {
			$menu_id = $menu->term_id;
			$t['skipped']++;
		}
		$shop_url = function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) > 0
			? get_permalink( wc_get_page_id( 'shop' ) )
			: home_url( '/shop/' );
		$items = [
			[ __( 'Shop', 'touchgrass-core' ), $shop_url ],
			[ __( 'Why grass?', 'touchgrass-core' ), home_url( '/#how' ) ],
			[ __( 'Reviews', 'touchgrass-core' ), home_url( '/#proof' ) ],
			[ __( 'FAQ', 'touchgrass-core' ), home_url( '/#faq' ) ],
		];
		$existing_titles = [];
		$menu_items = wp_get_nav_menu_items( $menu_id );
		if ( is_array( $menu_items ) ) {
			foreach ( $menu_items as $item ) {
				$existing_titles[] = $item->title;
			}
		}
		$position = count( $existing_titles ) + 1;
		foreach ( $items as $entry ) {
			if ( in_array( $entry[0], $existing_titles, true ) ) {
				$t['skipped']++;
				continue;
			}
			$result = wp_update_nav_menu_item( $menu_id, 0, [
				'menu-item-title'    => $entry[0],
				'menu-item-url'      => $entry[1],
				'menu-item-status'   => 'publish',
				'menu-item-position' => $position++,
			] );
			if ( is_wp_error( $result ) || ! $result ) { $t['failed']++; } else { $t['created']++; }
		}
		/* Assign to the primary location only if nothing is assigned yet. */
		$locations = get_nav_menu_locations();
		if ( empty( $locations['primary'] ) ) {
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
		return $t;
	}
}
