<?php
/**
 * Touch Grass Customizer: every editable brand string, image, URL, and
 * visibility switch lives here. The theme never hard-codes business copy —
 * it reads these. tg_brand_fields() is the single source of truth for
 * defaults, so fresh installs render correctly before the Customizer is
 * ever opened.
 *
 * Field types: text, textarea, checkbox, url, number, image.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * All editable brand fields: key => [label, default, type, section?, description?].
 *
 * @return array
 */
function tg_brand_fields() {
	return [
		/* Announcement bar */
		'tg_announcement_show' => [
			'label'   => __( 'Show announcement bar', 'touchgrass' ),
			'default' => true,
			'type'    => 'checkbox',
			'section' => 'tg_bar',
		],
		'tg_announcement_text' => [
			'label'   => __( 'Announcement text', 'touchgrass' ),
			'default' => __( 'Fresh Cut Friday — <strong>20% off</strong> with code <code>GOOUTSIDE</code>, for the irony.', 'touchgrass' ),
			'type'    => 'textarea',
			'section' => 'tg_bar',
			'description' => __( 'Keep this consistent with a real coupon in WooCommerce → Coupons. Links allowed.', 'touchgrass' ),
		],
		/* Hero */
		'tg_hero_image' => [
			'label'   => __( 'Hero image', 'touchgrass' ),
			'default' => '',
			'type'    => 'image',
			'section' => 'tg_hero',
			'description' => __( 'Empty = the theme’s default grass photo.', 'touchgrass' ),
		],
		'tg_hero_eyebrow' => [
			'label'   => __( 'Hero eyebrow', 'touchgrass' ),
			'default' => __( 'Cultivated indoors. Like you.', 'touchgrass' ),
			'type'    => 'text',
			'section' => 'tg_hero',
		],
		'tg_hero_headline' => [
			'label'   => __( 'Hero headline', 'touchgrass' ),
			'default' => __( 'Go ahead.<br>Touch <em>grass</em>.', 'touchgrass' ),
			'type'    => 'textarea',
			'section' => 'tg_hero',
		],
		'tg_hero_sub' => [
			'label'   => __( 'Hero sub-copy', 'touchgrass' ),
			'default' => __( 'Hand-grown plots of real grass, shipped to your desk. Going outside is free. This is $29. You do the math.', 'touchgrass' ),
			'type'    => 'textarea',
			'section' => 'tg_hero',
		],
		'tg_hero_cta_primary' => [
			'label'   => __( 'Hero primary button label', 'touchgrass' ),
			'default' => __( 'Shop the grass', 'touchgrass' ),
			'type'    => 'text',
			'section' => 'tg_hero',
		],
		'tg_hero_cta_primary_url' => [
			'label'   => __( 'Hero primary button link', 'touchgrass' ),
			'default' => '',
			'type'    => 'url',
			'section' => 'tg_hero',
			'description' => __( 'Empty = your WooCommerce shop page.', 'touchgrass' ),
		],
		'tg_hero_cta_secondary' => [
			'label'   => __( 'Hero secondary button label', 'touchgrass' ),
			'default' => __( 'How it works', 'touchgrass' ),
			'type'    => 'text',
			'section' => 'tg_hero',
		],
		'tg_hero_cta_secondary_url' => [
			'label'   => __( 'Hero secondary button link', 'touchgrass' ),
			'default' => '#how',
			'type'    => 'url',
			'section' => 'tg_hero',
		],
		'tg_trust_rating_text' => [
			'label'   => __( 'Trust row: rating text', 'touchgrass' ),
			'default' => '',
			'type'    => 'text',
			'section' => 'tg_hero',
			'description' => __( 'Empty = computed live from product reviews; hidden when there are no reviews yet.', 'touchgrass' ),
		],
		/* Confession */
		'tg_confession_title' => [
			'label'   => __( 'Confession headline', 'touchgrass' ),
			'default' => __( 'Going outside is free.', 'touchgrass' ),
			'type'    => 'text',
			'section' => 'tg_confession',
		],
		'tg_confession_copy' => [
			'label'   => __( 'Confession copy', 'touchgrass' ),
			'default' => __( 'The sun is shining right now, at no charge. Parks are public. Fresh air costs nothing. And yet — here you are, on a website, about to spend $29 on a rectangle of grass. <b>Should you just go for a walk? Yes. Will you? No.</b> We can&rsquo;t stop you, and we won&rsquo;t. In fact: how can we make this easier for you?', 'touchgrass' ),
			'type'    => 'textarea',
			'section' => 'tg_confession',
		],
		'tg_confession_price' => [
			'label'   => __( 'Confession table: Touch Grass price', 'touchgrass' ),
			'default' => '$29',
			'type'    => 'text',
			'section' => 'tg_confession',
		],
		/* Shop section */
		'tg_section_shop' => [
			'label'   => __( 'Show shop section on homepage', 'touchgrass' ),
			'default' => true,
			'type'    => 'checkbox',
			'section' => 'tg_sections',
		],
		'tg_shop_title' => [
			'label'   => __( 'Shop heading', 'touchgrass' ),
			'default' => __( 'Nine rectangles* of grass.', 'touchgrass' ),
			'type'    => 'text',
			'section' => 'tg_shop',
		],
		'tg_shop_sub' => [
			'label'   => __( 'Shop subheading', 'touchgrass' ),
			'default' => __( '*Two are circles. Two are not grass. We counted them anyway.', 'touchgrass' ),
			'type'    => 'text',
			'section' => 'tg_shop',
		],
		'tg_products_per_page' => [
			'label'   => __( 'Products per shop page', 'touchgrass' ),
			'default' => 9,
			'type'    => 'number',
			'section' => 'tg_shop',
		],
		/* How it works */
		'tg_how_image' => [
			'label'   => __( '“How it works” image', 'touchgrass' ),
			'default' => '',
			'type'    => 'image',
			'section' => 'tg_how',
			'description' => __( 'Empty = the theme’s default photo.', 'touchgrass' ),
		],
		'tg_step_1_title' => [ 'label' => __( 'Step 1 title', 'touchgrass' ), 'default' => __( 'Admit it.', 'touchgrass' ), 'type' => 'text', 'section' => 'tg_how' ],
		'tg_step_1_text'  => [ 'label' => __( 'Step 1 text', 'touchgrass' ), 'default' => __( 'You need grass. You won&rsquo;t go outside. This is fine. Everyone here has agreed not to mention it.', 'touchgrass' ), 'type' => 'textarea', 'section' => 'tg_how' ],
		'tg_step_2_title' => [ 'label' => __( 'Step 2 title', 'touchgrass' ), 'default' => __( 'Pay us.', 'touchgrass' ), 'type' => 'text', 'section' => 'tg_how' ],
		'tg_step_2_text'  => [ 'label' => __( 'Step 2 text', 'touchgrass' ), 'default' => __( 'Pick a plot. We ship within 48 hours. The money part is the easiest part — for us.', 'touchgrass' ), 'type' => 'textarea', 'section' => 'tg_how' ],
		'tg_step_3_title' => [ 'label' => __( 'Step 3 title', 'touchgrass' ), 'default' => __( 'Touch it.', 'touchgrass' ), 'type' => 'text', 'section' => 'tg_how' ],
		'tg_step_3_text'  => [ 'label' => __( 'Step 3 text', 'touchgrass' ), 'default' => __( 'Daily. That is the entire program.', 'touchgrass' ), 'type' => 'textarea', 'section' => 'tg_how' ],
		/* Reviews */
		'tg_proof_title' => [
			'label'   => __( 'Reviews heading', 'touchgrass' ),
			'default' => __( '8,600 indoor humans.<br>Zero walks taken.', 'touchgrass' ),
			'type'    => 'textarea',
			'section' => 'tg_proof',
		],
		'tg_proof_sub' => [
			'label'   => __( 'Reviews subheading', 'touchgrass' ),
			'default' => __( 'Every review from someone who chose $29 over free.', 'touchgrass' ),
			'type'    => 'text',
			'section' => 'tg_proof',
		],
		'tg_stat_1_value' => [ 'label' => __( 'Stat 1 value', 'touchgrass' ), 'default' => __( '8,600+', 'touchgrass' ), 'type' => 'text', 'section' => 'tg_proof' ],
		'tg_stat_1_label' => [ 'label' => __( 'Stat 1 label', 'touchgrass' ), 'default' => __( 'verified reviews', 'touchgrass' ), 'type' => 'text', 'section' => 'tg_proof' ],
		'tg_stat_2_value' => [ 'label' => __( 'Stat 2 value', 'touchgrass' ), 'default' => __( '4.9', 'touchgrass' ), 'type' => 'text', 'section' => 'tg_proof' ],
		'tg_stat_2_label' => [ 'label' => __( 'Stat 2 label', 'touchgrass' ), 'default' => __( 'average rating', 'touchgrass' ), 'type' => 'text', 'section' => 'tg_proof' ],
		'tg_stat_3_value' => [ 'label' => __( 'Stat 3 value', 'touchgrass' ), 'default' => __( '0', 'touchgrass' ), 'type' => 'text', 'section' => 'tg_proof' ],
		'tg_stat_3_label' => [ 'label' => __( 'Stat 3 label', 'touchgrass' ), 'default' => __( 'walks taken', 'touchgrass' ), 'type' => 'text', 'section' => 'tg_proof' ],
		/* Guarantees (used on the homepage AND the product page) */
		'tg_guarantee_1_title' => [ 'label' => __( 'Guarantee 1 title', 'touchgrass' ), 'default' => __( '30-day regrow guarantee', 'touchgrass' ), 'type' => 'text', 'section' => 'tg_guarantees' ],
		'tg_guarantee_1_text'  => [ 'label' => __( 'Guarantee 1 text', 'touchgrass' ), 'default' => __( 'If your grass dies within 30 days, a replacement ships free. No interrogation.', 'touchgrass' ), 'type' => 'textarea', 'section' => 'tg_guarantees' ],
		'tg_guarantee_2_title' => [ 'label' => __( 'Guarantee 2 title', 'touchgrass' ), 'default' => __( 'Free shipping over $50', 'touchgrass' ), 'type' => 'text', 'section' => 'tg_guarantees' ],
		'tg_guarantee_2_text'  => [ 'label' => __( 'Guide 2 text — keep consistent with WooCommerce → Settings → Shipping', 'touchgrass' ), 'default' => __( 'Orders over $50 ship free. The grass travels better than you do.', 'touchgrass' ), 'type' => 'textarea', 'section' => 'tg_guarantees' ],
		'tg_guarantee_3_title' => [ 'label' => __( 'Guarantee 3 title', 'touchgrass' ), 'default' => __( 'No sunlight required', 'touchgrass' ), 'type' => 'text', 'section' => 'tg_guarantees' ],
		'tg_guarantee_3_text'  => [ 'label' => __( 'Guarantee 3 text', 'touchgrass' ), 'default' => __( 'Grown for desks, basements, and other places the sun forgot.', 'touchgrass' ), 'type' => 'textarea', 'section' => 'tg_guarantees' ],
		/* Footer */
		'tg_footer_tagline' => [
			'label'   => __( 'Footer tagline', 'touchgrass' ),
			'default' => __( 'Premium plots of real grass for people who live indoors. Grown with care, shipped with speed, touched with joy.', 'touchgrass' ),
			'type'    => 'textarea',
			'section' => 'tg_footer',
		],
		'tg_footer_credit' => [
			'label'   => __( 'Footer credit line', 'touchgrass' ),
			'default' => __( 'Made with chlorophyll.', 'touchgrass' ),
			'type'    => 'text',
			'section' => 'tg_footer',
		],
		'tg_footer_signoff' => [
			'label'   => __( 'Footer signoff', 'touchgrass' ),
			'default' => __( 'Going outside is still free.', 'touchgrass' ),
			'type'    => 'text',
			'section' => 'tg_footer',
		],
		/* Section visibility */
		'tg_section_confession' => [ 'label' => __( 'Show “honesty” section', 'touchgrass' ), 'default' => true, 'type' => 'checkbox', 'section' => 'tg_sections' ],
		'tg_section_how'        => [ 'label' => __( 'Show “how it works” section', 'touchgrass' ), 'default' => true, 'type' => 'checkbox', 'section' => 'tg_sections' ],
		'tg_section_proof'      => [ 'label' => __( 'Show reviews section', 'touchgrass' ), 'default' => true, 'type' => 'checkbox', 'section' => 'tg_sections' ],
		'tg_section_faq'        => [ 'label' => __( 'Show FAQ section', 'touchgrass' ), 'default' => true, 'type' => 'checkbox', 'section' => 'tg_sections' ],
		'tg_section_news'       => [ 'label' => __( 'Show newsletter section', 'touchgrass' ), 'default' => true, 'type' => 'checkbox', 'section' => 'tg_sections' ],
		/* Legacy keys kept for backward compatibility */
		'tg_promo_code' => [
			'label'   => __( 'Promo code (displayed in announcements)', 'touchgrass' ),
			'default' => 'GOOUTSIDE',
			'type'    => 'text',
			'section' => 'tg_bar',
			'description' => __( 'Display only. The actual discount lives in WooCommerce → Coupons.', 'touchgrass' ),
		],
	];
}

function tg_customize_sections() {
	return [
		'tg_bar'        => __( 'Announcement bar', 'touchgrass' ),
		'tg_hero'       => __( 'Homepage hero', 'touchgrass' ),
		'tg_confession' => __( 'Honesty section', 'touchgrass' ),
		'tg_shop'       => __( 'Shop section', 'touchgrass' ),
		'tg_how'        => __( 'How it works', 'touchgrass' ),
		'tg_proof'      => __( 'Reviews', 'touchgrass' ),
		'tg_guarantees' => __( 'Guarantees', 'touchgrass' ),
		'tg_footer'     => __( 'Footer', 'touchgrass' ),
		'tg_sections'   => __( 'Section visibility', 'touchgrass' ),
	];
}

function tg_customize_register( $wp_customize ) {
	$wp_customize->add_panel( 'tg_brand_panel', [
		'title'    => __( 'Touch Grass', 'touchgrass' ),
		'priority' => 30,
	] );
	foreach ( tg_customize_sections() as $id => $title ) {
		$wp_customize->add_section( $id, [
			'title'    => $title,
			'priority' => 40,
			'panel'    => 'tg_brand_panel',
		] );
	};

	foreach ( tg_brand_fields() as $key => $args ) {
		$type = $args['type'];
		$setting_args = [
			'default'   => $args['default'],
			'transport' => 'refresh',
		];
		switch ( $type ) {
			case 'textarea':
				$setting_args['sanitize_callback'] = 'wp_kses_post';
				break;
			case 'checkbox':
				$setting_args['sanitize_callback'] = 'tg_sanitize_checkbox';
				break;
			case 'url':
				$setting_args['sanitize_callback'] = 'esc_url_raw';
				break;
			case 'number':
				$setting_args['sanitize_callback'] = 'absint';
				break;
			case 'image':
				$setting_args['sanitize_callback'] = 'absint';
				break;
			default:
				$setting_args['sanitize_callback'] = 'sanitize_text_field';
		}
		$wp_customize->add_setting( $key, $setting_args );

		$control_args = [
			'label'       => $args['label'],
			'section'     => $args['section'],
			'description' => isset( $args['description'] ) ? $args['description'] : '',
		];
		if ( 'checkbox' === $type ) {
			$control_args['type'] = 'checkbox';
			$wp_customize->add_control( $key, $control_args );
		} elseif ( 'image' === $type ) {
			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $key, $control_args ) );
		} elseif ( 'url' === $type ) {
			$control_args['type'] = 'url';
			$wp_customize->add_control( $key, $control_args );
		} elseif ( 'number' === $type ) {
			$control_args['type'] = 'number';
			$control_args['input_attrs'] = [ 'min' => 1, 'max' => 48, 'step' => 1 ];
			$wp_customize->add_control( $key, $control_args );
		} else {
			$control_args['type'] = $type;
			$wp_customize->add_control( $key, $control_args );
		}
	}
}
add_action( 'customize_register', 'tg_customize_register' );

function tg_sanitize_checkbox( $value ) {
	return $value ? true : false;
}

/**
 * Get a Touch Grass brand string (Customizer value, or default on fresh installs).
 *
 * @param string $key Setting key.
 * @return mixed
 */
function tg_brand( $key ) {
	$fields  = tg_brand_fields();
	$default = isset( $fields[ $key ] ) ? $fields[ $key ]['default'] : '';
	return get_theme_mod( $key, $default );
}

/**
 * HTML allowed in brand strings that may contain markup.
 *
 * @return array
 */
function tg_brand_kses() {
	return [
		'a'      => [ 'href' => [], 'title' => [], 'target' => [], 'rel' => [] ],
		'br'     => [],
		'em'     => [],
		'strong' => [],
		'b'      => [],
		'code'   => [ 'class' => [] ],
		'span'   => [ 'class' => [] ],
	];
}
