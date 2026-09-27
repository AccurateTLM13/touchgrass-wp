<?php
/**
 * Touch Grass Customizer: every editable brand string lives here.
 * The theme never hard-codes business copy — it reads these.
 * tg_brand_fields() is the single source of truth for defaults,
 * so fresh installs render correctly before the Customizer is ever opened.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * All editable brand fields: key => [label, default, type].
 *
 * @return array
 */
function tg_brand_fields() {
	return [
		'tg_announcement_text' => [
			'label'   => __( 'Announcement bar', 'touchgrass' ),
			'default' => __( 'Fresh Cut Friday — <strong>20% off</strong> with code <code>GOOUTSIDE</code>, for the irony.', 'touchgrass' ),
			'type'    => 'textarea',
		],
		'tg_hero_eyebrow' => [
			'label'   => __( 'Hero eyebrow', 'touchgrass' ),
			'default' => __( 'Cultivated indoors. Like you.', 'touchgrass' ),
			'type'    => 'text',
		],
		'tg_hero_headline' => [
			'label'   => __( 'Hero headline', 'touchgrass' ),
			'default' => __( 'Go ahead.<br>Touch <em>grass</em>.', 'touchgrass' ),
			'type'    => 'textarea',
		],
		'tg_hero_sub' => [
			'label'   => __( 'Hero sub-copy', 'touchgrass' ),
			'default' => __( 'Hand-grown plots of real grass, shipped to your desk. Going outside is free. This is $29. You do the math.', 'touchgrass' ),
			'type'    => 'textarea',
		],
		'tg_hero_cta_primary' => [
			'label'   => __( 'Hero primary button label', 'touchgrass' ),
			'default' => __( 'Shop the grass', 'touchgrass' ),
			'type'    => 'text',
		],
		'tg_hero_cta_secondary' => [
			'label'   => __( 'Hero secondary button label', 'touchgrass' ),
			'default' => __( 'How it works', 'touchgrass' ),
			'type'    => 'text',
		],
		'tg_confession_title' => [
			'label'   => __( 'Confession headline', 'touchgrass' ),
			'default' => __( 'Going outside is free.', 'touchgrass' ),
			'type'    => 'text',
		],
		'tg_confession_copy' => [
			'label'   => __( 'Confession copy', 'touchgrass' ),
			'default' => __( 'The sun is shining right now, at no charge. Parks are public. Fresh air costs nothing. And yet — here you are, on a website, about to spend $29 on a rectangle of grass. <b>Should you just go for a walk? Yes. Will you? No.</b> We can&rsquo;t stop you, and we won&rsquo;t. In fact: how can we make this easier for you?', 'touchgrass' ),
			'type'    => 'textarea',
		],
		'tg_footer_tagline' => [
			'label'   => __( 'Footer tagline', 'touchgrass' ),
			'default' => __( 'Premium plots of real grass for people who live indoors. Grown with care, shipped with speed, touched with joy.', 'touchgrass' ),
			'type'    => 'textarea',
		],
		'tg_promo_code' => [
			'label'   => __( 'Promo code (displayed in announcements)', 'touchgrass' ),
			'default' => 'GOOUTSIDE',
			'type'    => 'text',
		],
		'tg_guarantee_1_title' => [ 'label' => __( 'Guarantee 1 title', 'touchgrass' ), 'default' => __( '30-day regrow guarantee', 'touchgrass' ), 'type' => 'text' ],
		'tg_guarantee_1_text'  => [ 'label' => __( 'Guarantee 1 text', 'touchgrass' ), 'default' => __( 'If your grass dies within 30 days, a replacement ships free. No interrogation.', 'touchgrass' ), 'type' => 'textarea' ],
		'tg_guarantee_2_title' => [ 'label' => __( 'Guarantee 2 title', 'touchgrass' ), 'default' => __( 'Free shipping over $50', 'touchgrass' ), 'type' => 'text' ],
		'tg_guarantee_2_text'  => [ 'label' => __( 'Guarantee 2 text', 'touchgrass' ), 'default' => __( 'Orders over $50 ship free. The grass travels better than you do.', 'touchgrass' ), 'type' => 'textarea' ],
		'tg_guarantee_3_title' => [ 'label' => __( 'Guarantee 3 title', 'touchgrass' ), 'default' => __( 'No sunlight required', 'touchgrass' ), 'type' => 'text' ],
		'tg_guarantee_3_text'  => [ 'label' => __( 'Guarantee 3 text', 'touchgrass' ), 'default' => __( 'Grown for desks, basements, and other places the sun forgot.', 'touchgrass' ), 'type' => 'textarea' ],
		'tg_step_1_title' => [ 'label' => __( 'Step 1 title', 'touchgrass' ), 'default' => __( 'Admit it.', 'touchgrass' ), 'type' => 'text' ],
		'tg_step_1_text'  => [ 'label' => __( 'Step 1 text', 'touchgrass' ), 'default' => __( 'You need grass. You won&rsquo;t go outside. This is fine. Everyone here has agreed not to mention it.', 'touchgrass' ), 'type' => 'textarea' ],
		'tg_step_2_title' => [ 'label' => __( 'Step 2 title', 'touchgrass' ), 'default' => __( 'Pay us.', 'touchgrass' ), 'type' => 'text' ],
		'tg_step_2_text'  => [ 'label' => __( 'Step 2 text', 'touchgrass' ), 'default' => __( 'Pick a plot. We ship within 48 hours. The money part is the easiest part — for us.', 'touchgrass' ), 'type' => 'textarea' ],
		'tg_step_3_title' => [ 'label' => __( 'Step 3 title', 'touchgrass' ), 'default' => __( 'Touch it.', 'touchgrass' ), 'type' => 'text' ],
		'tg_step_3_text'  => [ 'label' => __( 'Step 3 text', 'touchgrass' ), 'default' => __( 'Daily. That is the entire program.', 'touchgrass' ), 'type' => 'textarea' ],
	];
}

function tg_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'tg_brand', [
		'title'    => __( 'Touch Grass', 'touchgrass' ),
		'priority' => 30,
	] );

	foreach ( tg_brand_fields() as $key => $args ) {
		$wp_customize->add_setting( $key, [
			'default'           => $args['default'],
			'sanitize_callback' => $args['type'] === 'textarea' ? 'wp_kses_post' : 'sanitize_text_field',
			'transport'         => 'refresh',
		] );
		$wp_customize->add_control( $key, [
			'label'   => $args['label'],
			'section' => 'tg_brand',
			'type'    => $args['type'],
		] );
	}
}
add_action( 'customize_register', 'tg_customize_register' );

/**
 * Get a Touch Grass brand string (Customizer value, or default on fresh installs).
 *
 * @param string $key Setting key.
 * @return string
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
		'br'     => [],
		'em'     => [],
		'strong' => [],
		'b'      => [],
		'code'   => [ 'class' => [] ],
		'span'   => [ 'class' => [] ],
	];
}
