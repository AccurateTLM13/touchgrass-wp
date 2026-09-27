<?php
/**
 * Idempotent demo-content importer for the Touch Grass store.
 *
 * Safe to run any number of times: every object is looked up before it is
 * created, and existing objects are updated in place instead of duplicated.
 *
 * - Products: looked up by SKU (contract table).
 * - Categories: looked up by slug (plots, accessories).
 * - Media: looked up by attachment slug before sideloading from assets/demo/.
 * - Coupon: looked up by code via wc_get_coupon_id_by_code().
 * - FAQs / testimonials: looked up by post slug before wp_insert_post().
 * - Menu "Primary": looked up by name; items checked by title.
 *
 * Pure PHP — no WP-CLI, no SSH. Runs from the admin dashboard.
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
			[ 'Is the grass real?', 'Yes. This is our most common question and, frankly, the most insulting. It is real grass, grown in a real greenhouse, by people with dirt under their nails.' ],
			[ 'How often do I water it?', 'A light mist every two to three days. The Mister exists for exactly this ritual.' ],
			[ 'Will it survive my apartment?', 'If you survive your apartment, the grass will. It asks less of you than your houseplants did.' ],
			[ 'How fast is shipping?', 'Orders leave the greenhouse within 48 hours. The grass travels better than you do.' ],
			[ 'What if my grass dies?', 'Then it lived a short, beautiful, indoor life. The 30-day regrow guarantee means a replacement ships free. No interrogation.' ],
			[ "Isn't going outside free?", "Yes. And yet here you are. We're not judging. We're invoicing." ],
			[ 'Is this a joke?', 'The grass is real. The price is real. Your refusal to go outside is real. Which part sounds like a joke?' ],
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
	 * Run the full import. Returns an array of step => [ created, updated, skipped ].
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

		// Take the storefront live: a fresh WooCommerce install hides the
		// catalog behind "coming soon" until launched. The demo is meant
		// to be seen the moment the import finishes.
		if ( 'yes' === get_option( 'woocommerce_coming_soon' ) ) {
			update_option( 'woocommerce_coming_soon', 'no' );
			$report['store_live'] = [ 'created' => 1, 'updated' => 0, 'skipped' => 0 ];
		} else {
			$report['store_live'] = [ 'created' => 0, 'updated' => 0, 'skipped' => 1 ];
		}

		return $report;
	}

	protected static function tally() {
		return [ 'created' => 0, 'updated' => 0, 'skipped' => 0 ];
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
			if ( ! is_wp_error( $result ) ) { $t['created']++; }
		}
		return $t;
	}

	/* ---------- media ---------- */

	protected static function import_media() {
		$t = self::tally();
		$map = [];
		$dir = TG_CORE_PATH . 'assets/demo/';
		foreach ( glob( $dir . '*.webp' ) as $file ) {
			$slug = pathinfo( $file, PATHINFO_FILENAME );
			$existing = get_page_by_path( $slug, OBJECT, 'attachment' );
			if ( $existing ) {
				$map[ $slug ] = (int) $existing->ID;
				$t['skipped']++;
				continue;
			}
			$file_array = [
				'name'     => basename( $file ),
				'type'     => 'image/webp',
				'tmp_name' => $file,
				'error'    => 0,
				'size'     => filesize( $file ),
			];
			$att_id = media_handle_sideload( $file_array, 0 );
			if ( is_wp_error( $att_id ) ) { continue; }
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
		foreach ( self::products() as $sku => $data ) {
			$product_id = function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product_id_by_sku( $sku ) : 0;
			if ( $product_id ) {
				$product = wc_get_product( $product_id );
				$t['updated']++;
			} else {
				$product = new WC_Product_Simple();
				$product->set_sku( $sku );
				$product->set_status( 'publish' );
				$product->set_catalog_visibility( 'visible' );
				$t['created']++;
			}
			$product->set_name( $data['name'] );
			$product->set_description( $data['description'] );
			$product->set_short_description( $data['tagline'] );
			$product->set_regular_price( (string) $data['price'] );
			$product->set_sale_price( $data['sale'] ? (string) $data['sale'] : '' );
			$product->set_price( $data['sale'] ? (string) $data['sale'] : (string) $data['price'] );
			$ids = [];
			foreach ( $data['cats'] as $slug ) {
				if ( isset( $cat_ids[ $slug ] ) ) { $ids[] = $cat_ids[ $slug ]; }
			}
			if ( $ids ) { $product->set_category_ids( $ids ); }
			if ( isset( $media_map[ $data['image'] ] ) ) {
				$product->set_image_id( $media_map[ $data['image'] ] );
			}
			$product->update_meta_data( '_tg_tagline', $data['tagline'] );
			$product->update_meta_data( '_tg_badge', $data['badge'] );
			$product->save();
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
		$coupon->set_description( __( 'Fresh Cut Friday — 20% off, for the irony.', 'touchgrass-core' ) );
		$coupon->save();
		$t['created']++;
		return $t;
	}

	/* ---------- FAQs ---------- */

	protected static function import_faqs() {
		$t = self::tally();
		$order = 0;
		foreach ( self::faqs() as $faq ) {
			$order += 10;
			$slug = sanitize_title( $faq[0] );
			$existing = get_page_by_path( $slug, OBJECT, 'tg_faq' );
			if ( $existing ) {
				$t['skipped']++;
				continue;
			}
			$post_id = wp_insert_post( [
				'post_type'   => 'tg_faq',
				'post_title'  => $faq[0],
				'post_name'   => $slug,
				'post_content' => $faq[1],
				'post_status' => 'publish',
			] );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				update_post_meta( $post_id, '_tg_faq_order', $order );
				$t['created']++;
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
				$t['created']++;
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
			if ( is_wp_error( $menu_id ) ) { return $t; }
		} else {
			$menu_id = $menu->term_id;
			$t['skipped']++;
		}
		$items = [
			[ __( 'Shop', 'touchgrass-core' ), 'shop' ],
			[ __( 'Why grass?', 'touchgrass-core' ), '#how' ],
			[ __( 'Reviews', 'touchgrass-core' ), '#proof' ],
			[ __( 'FAQ', 'touchgrass-core' ), '#faq' ],
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
			$url = ( $entry[1] === 'shop' ) ? home_url( '/shop/' ) : home_url( '/' . $entry[1] );
			wp_update_nav_menu_item( $menu_id, 0, [
				'menu-item-title'    => $entry[0],
				'menu-item-url'      => $url,
				'menu-item-status'   => 'publish',
				'menu-item-position' => $position++,
			] );
			$t['created']++;
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
