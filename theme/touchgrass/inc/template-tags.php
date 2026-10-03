<?php
/**
 * Template tags: FAQ and testimonial queries, ratings, URLs, branding.
 *
 * Fallback policy: the fictional default FAQs/testimonials render only when
 * the core plugin (and therefore the tg_faq / tg_testimonial post types) is
 * not present at all — e.g. a theme-only install previewing the design. Once
 * the plugin is active, what the merchant published is what shows: if they
 * deleted every testimonial, the reviews section hides instead of resurrecting
 * fictional quotes.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Default FAQs, used only when the tg_faq post type doesn't exist.
 *
 * @return array [question, answer][]
 */
function tg_default_faqs() {
	return [
		[ __( 'Is the grass real?', 'touchgrass' ), __( 'Yes. This is our most common question and, frankly, the most insulting. It is real grass, grown in a real greenhouse, by people with dirt under their nails.', 'touchgrass' ) ],
		[ __( 'How often do I water it?', 'touchgrass' ), __( 'A light mist every two to three days. The Mister exists for exactly this ritual.', 'touchgrass' ) ],
		[ __( 'Will it survive my apartment?', 'touchgrass' ), __( 'If you survive your apartment, the grass will. It asks less of you than your houseplants did.', 'touchgrass' ) ],
		[ __( 'How fast is shipping?', 'touchgrass' ), __( 'Orders leave the greenhouse within 48 hours. The grass travels better than you do.', 'touchgrass' ) ],
		[ __( 'What if my grass dies?', 'touchgrass' ), __( 'Then it lived a short, beautiful, indoor life. The 30-day regrow guarantee means a replacement ships free. No interrogation.', 'touchgrass' ) ],
		[ __( "Isn't going outside free?", 'touchgrass' ), __( "Yes. And yet here you are. We're not judging. We're invoicing.", 'touchgrass' ) ],
		[ __( 'Is this a joke?', 'touchgrass' ), __( 'The grass is real. The price is real. Your refusal to go outside is real. Which part sounds like a joke?', 'touchgrass' ) ],
	];
}

/**
 * Get FAQs ordered by the saved _tg_faq_order field (lower first).
 *
 * @return array [question, answer][]
 */
function tg_get_faqs() {
	if ( post_type_exists( 'tg_faq' ) ) {
		$posts = get_posts( [
			'post_type'      => 'tg_faq',
			'posts_per_page' => 40,
			'post_status'    => 'publish',
			'meta_key'       => '_tg_faq_order',
			'orderby'        => [ 'meta_value_num' => 'ASC', 'date' => 'ASC' ],
		] );
		if ( $posts ) {
			$faqs = [];
			foreach ( $posts as $p ) {
				$faqs[] = [ get_the_title( $p ), $p->post_content ];
			}
			return $faqs;
		}
		return [];
	}
	return tg_default_faqs();
}

/**
 * Default testimonials, used only when the tg_testimonial post type doesn't exist.
 *
 * @return array [quote, name, role, rating][]
 */
function tg_default_testimonials() {
	return [
		[ __( '"I bought it as a joke for our hackathon team. It is now the most important object in the office. Our standups are 40% calmer."', 'touchgrass' ), 'Devon K.', __( 'Backend dev · The Daily Driver', 'touchgrass' ), 5 ],
		[ __( '"My therapist asked what changed. I said grass. She wrote it down."', 'touchgrass' ), 'Priya S.', __( 'ML engineer · The Pro Max', 'touchgrass' ), 5 ],
		[ __( '"It sits next to my monitor. I have named it. We are close."', 'touchgrass' ), 'Marcus T.', __( 'Frontend dev · The Night Shift', 'touchgrass' ), 5 ],
	];
}

/**
 * Get testimonials: published tg_testimonial posts; [] when the merchant
 * deleted them all; fictional defaults only when the plugin is absent.
 *
 * @return array [quote, name, role, rating][]
 */
function tg_get_testimonials() {
	if ( post_type_exists( 'tg_testimonial' ) ) {
		$posts = get_posts( [
			'post_type'      => 'tg_testimonial',
			'posts_per_page' => 6,
			'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'ASC' ],
			'post_status'    => 'publish',
		] );
		$out = [];
		foreach ( $posts as $p ) {
			$out[] = [
				$p->post_content,
				get_the_title( $p ),
				get_post_meta( $p->ID, '_tg_role', true ),
				(int) get_post_meta( $p->ID, '_tg_rating', true ) ?: 5,
			];
		}
		return $out;
	}
	return tg_default_testimonials();
}

/**
 * Star rating HTML from a real product rating. Returns '' when the product
 * has no reviews — no fake five-star displays.
 *
 * @param WC_Product|int $product
 * @return string
 */
function tg_product_rating_html( $product ) {
	if ( ! $product instanceof WC_Product ) {
		if ( ! function_exists( 'wc_get_product' ) ) { return ''; }
		$product = wc_get_product( (int) $product );
	}
	if ( ! $product ) { return ''; }
	$count = (int) $product->get_review_count();
	if ( $count <= 0 ) { return ''; }
	$rating = (float) $product->get_average_rating();
	return tg_stars( $rating, $count );
}

/**
 * Trust-row rating text computed from live product reviews, e.g.
 * "4.9 from 8,600+ reviews". Returns '' when there are no reviews yet.
 *
 * @return string
 */
function tg_live_rating_text() {
	if ( ! function_exists( 'wc_get_products' ) ) { return ''; }
	$products = wc_get_products( [ 'limit' => -1, 'status' => 'publish' ] );
	$total = 0; $weighted = 0.0;
	foreach ( $products as $product ) {
		$count = (int) $product->get_review_count();
		if ( $count > 0 ) {
			$total += $count;
			$weighted += (float) $product->get_average_rating() * $count;
		}
	}
	if ( $total <= 0 ) { return ''; }
	$avg = $weighted / $total;
	return sprintf(
		/* translators: 1: average rating, 2: review count */
		__( '%1$s from %2$s reviews', 'touchgrass' ),
		number_format_i18n( $avg, 1 ),
		number_format_i18n( $total )
	);
}

/**
 * Shop URL from the configured WooCommerce shop page — never assumes /shop/.
 *
 * @return string
 */
function tg_shop_url() {
	if ( function_exists( 'wc_get_page_id' ) ) {
		$page_id = wc_get_page_id( 'shop' );
		if ( $page_id > 0 ) {
			$url = get_permalink( $page_id );
			if ( $url ) { return $url; }
		}
	}
	return home_url( '/shop/' );
}

/**
 * Whether WooCommerce is active (theme-side check; the plugin may be off).
 *
 * @return bool
 */
function tg_woo_active() {
	return class_exists( 'WooCommerce', false );
}

/**
 * Site wordmark: custom logo when set, otherwise the configured site title.
 */
function tg_wordmark() {
	if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	$name = get_bloginfo( 'name' );
	if ( ! $name ) { $name = __( 'Touch Grass', 'touchgrass' ); }
	echo '<a class="wordmark wordmark-text" href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( $name ) . '</a>';
}
