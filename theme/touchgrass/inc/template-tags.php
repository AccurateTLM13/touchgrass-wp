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
		[ __( 'Is the grass real?', 'touchgrass' ), __( 'Certified 100% real grass. *Grass.', 'touchgrass' ) ],
		[ __( 'Is this a joke?', 'touchgrass' ), __( 'Going outside is free. This is the paid alternative.', 'touchgrass' ) ],
		[ __( 'What if my grass dies?', 'touchgrass' ), __( 'Then it lived a short, beautiful, indoor life. 30-day replacement, no interrogation.', 'touchgrass' ) ],
		[ __( 'How often do I water it?', 'touchgrass' ), __( 'A light mist every two to three days. The Mister exists for exactly this ritual.', 'touchgrass' ) ],
		[ __( 'Can I eat it?', 'touchgrass' ), __( 'You can do anything once.', 'touchgrass' ) ],
		[ __( 'Do you ship internationally?', 'touchgrass' ), __( 'Currently the US only. The grass is patriotic, but we\'re working on it.', 'touchgrass' ) ],
		[ __( 'What\'s the difference between the plots?', 'touchgrass' ), __( 'Size, species, and attitude. The Daily Driver is the all-rounder; the Night Shift tolerates your cave.', 'touchgrass' ) ],
		[ __( 'Why does checkout say "Invoice"?', 'touchgrass' ), __( 'Because we\'re not judging. We\'re invoicing.', 'touchgrass' ) ],
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
 * Live review aggregate computed from published product reviews.
 *
 * @return array|null [ 'avg' => float, 'count' => int ] or null when there are no reviews yet.
 */
function tg_live_rating_data() {
	if ( ! function_exists( 'wc_get_products' ) ) { return null; }
	$products = wc_get_products( [ 'limit' => -1, 'status' => 'publish' ] );
	$total = 0; $weighted = 0.0;
	foreach ( $products as $product ) {
		$count = (int) $product->get_review_count();
		if ( $count > 0 ) {
			$total += $count;
			$weighted += (float) $product->get_average_rating() * $count;
		}
	}
	if ( $total <= 0 ) { return null; }
	return [ 'avg' => $weighted / $total, 'count' => $total ];
}

/**
 * Trust-row rating text computed from live product reviews, e.g.
 * "4.9 from 8,600+ reviews". Returns '' when there are no reviews yet.
 *
 * @return string
 */
function tg_live_rating_text() {
	$data = tg_live_rating_data();
	if ( ! $data ) { return ''; }
	return sprintf(
		/* translators: 1: average rating, 2: review count */
		__( '%1$s from %2$s reviews', 'touchgrass' ),
		number_format_i18n( $data['avg'], 1 ),
		number_format_i18n( $data['count'] )
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

/* Trust block: guarantee card + review slider + press strip + certification
 * line. Used on the homepage (full), every PDP, and the cart (compact —
 * PDPs already have their own reviews tab).
 *
 * Press outlets are deliberately fictional. Never substitute real
 * publications here.
 */
function tg_trust_block( $variant = 'full' ) {
	?>
	<section class="tg-trust tg-trust--<?php echo esc_attr( $variant ); ?>" aria-label="<?php esc_attr_e( 'Why trust Touch Grass', 'touchgrass' ); ?>">
		<div class="wrap">
			<?php tg_guarantee_card(); ?>
			<?php if ( 'full' === $variant ) : ?>
				<?php tg_render_testimonials(); ?>
			<?php endif; ?>
			<?php tg_press_strip(); ?>
			<p class="tg-cert"><?php esc_html_e( 'Certified 100% Real Grass*', 'touchgrass' ); ?> <span class="tg-cert-footnote"><?php esc_html_e( '*grass', 'touchgrass' ); ?></span></p>
		</div>
	</section>
	<?php
}

function tg_press_strip() {
	$quotes = [
		[ __( 'The future of touching grass.', 'touchgrass' ), 'Screen Time Weekly' ],
		[ __( 'We reviewed it between naps. Five naps.', 'touchgrass' ), 'The Daily Nap' ],
		[ __( 'Real grass, real invoice. Journalism.', 'touchgrass' ), 'Grassroots Quarterly' ],
	];
	?>
	<div class="tg-press" role="complementary" aria-label="<?php esc_attr_e( 'Press', 'touchgrass' ); ?>">
		<p class="tg-press-kicker"><?php esc_html_e( 'Press clippings, on file.', 'touchgrass' ); ?></p>
		<ul class="tg-press-list">
			<?php foreach ( $quotes as $q ) : ?>
				<li>
					<blockquote>&ldquo;<?php echo esc_html( $q[0] ); ?>&rdquo;</blockquote>
					<cite>&mdash; <?php echo esc_html( $q[1] ); ?></cite>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/**
 * Computed unit line: price per blade. Deadpan pricing transparency.
 *
 * @param WC_Product $product
 * @return string Empty when it can't be computed.
 */
function tg_per_blade_line( $product ) {
	if ( ! function_exists( 'tg_product_blades' ) || ! $product instanceof WC_Product ) {
		return '';
	}
	$blades = tg_product_blades( $product );
	$price  = $product->get_price();
	if ( ! $blades || ! is_numeric( $price ) || $price <= 0 ) {
		return '';
	}
	$per = (float) $price / $blades;
	$fmt = $per < 0.01 ? number_format( $per, 3 ) : number_format( $per, 2 );
	return sprintf( __( '≈ $%s per blade', 'touchgrass' ), $fmt );
}

/**
 * FAQPage JSON-LD for the homepage FAQ engine.
 */
function tg_faq_json_ld() {
	if ( ! function_exists( 'tg_get_faqs' ) ) {
		return;
	}
	$items = [];
	foreach ( tg_get_faqs() as $faq ) {
		if ( ! isset( $faq[0], $faq[1] ) ) {
			continue;
		}
		$items[] = [
			'@type'          => 'Question',
			'name'           => $faq[0],
			'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $faq[1] ],
		];
	}
	if ( ! $items ) {
		return;
	}
	$schema = [
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $items,
	];
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}

/* Guarantee card: the trust block's anchor. The closing line comes from the
 * microcopy map (cart_guarantee) so it stays editable. */
function tg_guarantee_card() {
	$line = function_exists( 'tg_microcopy' )
		? tg_microcopy( 'cart_guarantee' )
		: __( 'Your money is safe. For now.', 'touchgrass' );
	?>
	<div class="tg-guarantee-card">
		<p class="tg-guarantee-title"><?php esc_html_e( '30-Day Photosynthesis Promise', 'touchgrass' ); ?></p>
		<p class="tg-guarantee-text"><?php esc_html_e( 'If your grass dies within 30 days, we replace it. No interrogation.', 'touchgrass' ); ?></p>
		<p class="tg-guarantee-line"><?php echo esc_html( $line ); ?></p>
	</div>
	<?php
}

/**
 * "Complete the Ritual" cross-sells for the PDP.
 */
function tg_render_ritual_cross_sells() {
	if ( ! function_exists( 'tg_core_woo_active' ) || ! tg_core_woo_active() ) {
		return;
	}
	global $product;
	if ( ! $product instanceof WC_Product ) {
		return;
	}
	$ids = array_filter( array_map( 'intval', (array) $product->get_cross_sell_ids() ) );
	if ( ! $ids ) {
		return;
	}
	$cards = '';
	foreach ( $ids as $id ) {
		$p = wc_get_product( $id );
		if ( ! $p || ! $p->is_visible() ) {
			continue;
		}
		$cards .= tg_product_card( $p );
	}
	if ( '' === $cards ) {
		return;
	}
	echo '<section class="tg-ritual" aria-label="' . esc_attr__( 'Complete the Ritual', 'touchgrass' ) . '">';
	echo '<h2>' . esc_html__( 'Complete the Ritual', 'touchgrass' ) . '</h2>';
	echo '<div class="tg-ritual-grid">' . $cards . '</div>';
	echo '</section>';
}

/**
 * Single product card, shared by the estates grid and the ritual section.
 *
 * @param WC_Product $product
 * @return string HTML
 */
function tg_product_card( $product ) {
	$badge_label = function_exists( 'tg_product_badge' ) ? tg_product_badge( $product ) : '';
	$tagline     = function_exists( 'tg_product_tagline' ) ? tg_product_tagline( $product ) : '';
	ob_start();
	?>
	<article class="pcard">
		<a class="pcard-link" href="<?php echo esc_url( $product->get_permalink() ); ?>">
			<div class="ph"><?php echo $product->get_image( 'woocommerce_thumbnail', [ 'loading' => 'lazy' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<div class="pb">
				<span class="tag<?php echo $badge_label ? '' : ' tag-empty'; ?>"><?php echo $badge_label ? esc_html( $badge_label ) : '&nbsp;'; ?></span>
				<h3><?php echo esc_html( $product->get_name() ); ?></h3>
				<?php if ( $tagline ) : ?>
					<div class="sub"><?php echo esc_html( $tagline ); ?></div>
				<?php endif; ?>
				<div class="row">
					<span class="price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
				</div>
			</div>
		</a>
	</article>
	<?php
	return ob_get_clean();
}

/**
 * Product tabs rendered as accordions (Description / The Deed /
 * Shipping & Returns / How It Ships). Reviews are rendered separately.
 */
function tg_product_accordions() {
	$tabs = apply_filters( 'woocommerce_product_tabs', [] );
	unset( $tabs['reviews'] );
	if ( ! $tabs ) {
		return;
	}
	echo '<div class="tg-accordions">';
	foreach ( $tabs as $key => $tab ) {
		if ( empty( $tab['title'] ) || empty( $tab['callback'] ) ) {
			continue;
		}
		echo '<details class="tg-accordion" id="tg-tab-' . esc_attr( $key ) . '">';
		echo '<summary>' . esc_html( $tab['title'] ) . '</summary>';
		echo '<div class="tg-accordion-body">';
		call_user_func( $tab['callback'], $key, $tab );
		echo '</div></details>';
	}
	echo '</div>';
}

/**
 * Newsletter signup form. Reused by the footer newsletter section and the
 * Grass Club section — $id_suffix keeps element IDs unique per instance.
 * The consent line comes from the microcopy map.
 *
 * @param string $id_suffix Unique suffix for element IDs, e.g. 'club'.
 */
function tg_newsletter_form( $id_suffix = '' ) {
	$s = $id_suffix ? '-' . sanitize_html_class( $id_suffix ) : '';
	$consent = function_exists( 'tg_microcopy' )
		? tg_microcopy( 'newsletter_consent' )
		: __( 'No spam. The grass insists.', 'touchgrass' );
	?>
	<div class="tg-news-wrap">
		<form class="news-form tg-news-form" id="newsForm<?php echo esc_attr( $s ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
			<?php wp_nonce_field( 'tg_newsletter', 'tg_newsletter_nonce' ); ?>
			<label class="screen-reader-text" for="newsEmail<?php echo esc_attr( $s ); ?>"><?php esc_html_e( 'Email address', 'touchgrass' ); ?></label>
			<input type="email" id="newsEmail<?php echo esc_attr( $s ); ?>" name="email" placeholder="you@indoors.dev" autocomplete="email" required>
			<input type="text" name="tg_company" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="tg-honeypot">
			<button type="submit"><?php esc_html_e( 'Subscribe', 'touchgrass' ); ?></button>
		</form>
		<div class="news-fine" id="newsFine<?php echo esc_attr( $s ); ?>"><?php echo esc_html( $consent ); ?></div>
		<div class="news-done" id="newsDone<?php echo esc_attr( $s ); ?>" role="status"></div>
		<div class="news-error" id="newsError<?php echo esc_attr( $s ); ?>" role="alert"></div>
	</div>
	<?php
}
