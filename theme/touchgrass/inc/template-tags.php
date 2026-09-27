<?php
/**
 * Template tags: FAQ and testimonial queries with brand-voice fallbacks.
 * If the core plugin (or its demo content) isn't present, the homepage
 * still renders — with the default copy instead of empty sections.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Default FAQs, used when no tg_faq posts exist.
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
 * Get FAQs: tg_faq posts first, defaults when empty.
 *
 * @return array [question, answer][]
 */
function tg_get_faqs() {
	if ( post_type_exists( 'tg_faq' ) ) {
		$posts = get_posts( [
			'post_type'      => 'tg_faq',
			'posts_per_page' => 20,
			'orderby'        => [ 'menu_order' => 'ASC', 'date' => 'ASC' ],
			'post_status'    => 'publish',
		] );
		if ( $posts ) {
			$faqs = [];
			foreach ( $posts as $p ) {
				$faqs[] = [ get_the_title( $p ), $p->post_content ];
			}
			return $faqs;
		}
	}
	return tg_default_faqs();
}

/**
 * Default testimonials, used when no tg_testimonial posts exist.
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
 * Get testimonials: tg_testimonial posts first, defaults when empty.
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
		if ( $posts ) {
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
	}
	return tg_default_testimonials();
}
