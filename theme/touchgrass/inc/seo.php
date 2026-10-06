<?php
/**
 * SEO, social sharing, and structured data.
 *
 * Fills the gaps the theme is responsible for: meta description,
 * og:description, og:image (+ dimensions), twitter:image, and JSON-LD
 * (Product on PDPs; Organization + WebSite on the homepage).
 *
 * Deliberately does NOT output og:title, og:type, og:url, og:site_name,
 * og:locale, or twitter:card: those already come from a separate source
 * on production (not from this codebase), and duplicating them would
 * produce invalid double tags.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default social image: theme asset, 1200x630.
 *
 * @return string Absolute URL.
 */
function tg_og_image_default() {
	return esc_url( get_template_directory_uri() . '/assets/img/og-default.jpg' );
}

/**
 * Social image data for the current page: [ url, width, height, alt ].
 * Product pages use the featured image; everything else uses the default.
 *
 * @return array{0:string,1:int,2:int,3:string}
 */
function tg_og_image_data() {
	$default = [ tg_og_image_default(), 1200, 630, __( 'Touch Grass — premium plots of real grass', 'touchgrass' ) ];
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return $default;
	}
	$product = wc_get_product( get_the_ID() );
	if ( ! $product instanceof WC_Product ) {
		return $default;
	}
	$thumb_id = $product->get_image_id();
	if ( ! $thumb_id ) {
		return $default;
	}
	$src = wp_get_attachment_image_src( $thumb_id, 'full' );
	if ( ! $src ) {
		return $default;
	}
	return [ esc_url( $src[0] ), (int) $src[1], (int) $src[2], $product->get_name() ];
}

/**
 * Meta description for the current page, ~155 chars, word-boundary trimmed.
 *
 * @return string
 */
function tg_meta_description() {
	$desc = '';
	if ( function_exists( 'is_product' ) && is_product() ) {
		$product = wc_get_product( get_the_ID() );
		if ( $product instanceof WC_Product ) {
			$desc = $product->get_short_description();
			if ( '' === trim( wp_strip_all_tags( $desc ) ) && function_exists( 'tg_product_tagline' ) ) {
				$desc = tg_product_tagline( $product );
			}
		}
	} elseif ( is_front_page() && function_exists( 'tg_brand' ) ) {
		$desc = tg_brand( 'tg_hero_sub' );
	}
	$desc = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $desc ) ) );
	if ( '' === $desc ) {
		$desc = __( 'Premium plots of real grass for people who live indoors. Going outside is free. This is the paid alternative.', 'touchgrass' );
	}
	return wp_trim_words( $desc, 24, '...' );
}

/**
 * Head tags: description, og:description, og:image (+dims/alt), twitter:image.
 */
function tg_seo_head() {
	$desc = tg_meta_description();
	list( $img, $w, $h, $alt ) = tg_og_image_data();
	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
	echo '<meta property="og:image:width" content="' . absint( $w ) . '">' . "\n";
	echo '<meta property="og:image:height" content="' . absint( $h ) . '">' . "\n";
	echo '<meta property="og:image:alt" content="' . esc_attr( $alt ) . '">' . "\n";
	echo '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n";
}
add_action( 'wp_head', 'tg_seo_head', 5 );

/**
 * Product JSON-LD for PDPs. Availability reflects real stock status.
 *
 * @return array Schema array (empty when not applicable, for tests).
 */
function tg_product_schema() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return [];
	}
	$product = wc_get_product( get_the_ID() );
	if ( ! $product instanceof WC_Product ) {
		return [];
	}
	list( $img ) = tg_og_image_data();
	return [
		'@context'    => 'https://schema.org',
		'@type'       => 'Product',
		'name'        => $product->get_name(),
		'image'       => [ $img ],
		'description' => tg_meta_description(),
		'brand'       => [
			'@type' => 'Brand',
			'name'  => 'Surrey Grassworks',
		],
		'offers'      => [
			'@type'         => 'Offer',
			'url'           => $product->get_permalink(),
			'priceCurrency' => get_woocommerce_currency(),
			'price'         => $product->get_price(),
			'availability'  => $product->is_in_stock()
				? 'https://schema.org/InStock'
				: 'https://schema.org/OutOfStock',
		],
	];
}

/**
 * Organization + WebSite JSON-LD for the homepage.
 *
 * @return array Schema array (empty when not applicable, for tests).
 */
function tg_home_schema() {
	if ( ! is_front_page() ) {
		return [];
	}
	$home = home_url( '/' );
	return [
		'@context'   => 'https://schema.org',
		'@graph'     => [
			[
				'@type'  => 'Organization',
				'@id'    => $home . '#grassworks',
				'name'   => 'Surrey Grassworks',
				'url'    => $home,
				'logo'   => tg_og_image_default(),
				'slogan' => __( 'Going outside is free.', 'touchgrass' ),
			],
			[
				'@type' => 'WebSite',
				'@id'   => $home . '#website',
				'url'   => $home,
				'name'  => get_bloginfo( 'name' ),
			],
		],
	];
}

/**
 * Print Product / Organization+WebSite JSON-LD.
 */
function tg_seo_jsonld() {
	$schema = tg_product_schema();
	if ( ! $schema ) {
		$schema = tg_home_schema();
	}
	if ( ! $schema ) {
		return;
	}
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'tg_seo_jsonld', 6 );
