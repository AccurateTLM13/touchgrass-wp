<?php
/**
 * Front page: announcement → hero → estates grid → trust strip →
 * confession → Deed teaser → Grass Club → how it works → reviews →
 * FAQ → newsletter.
 *
 * All business copy is editable in the Customizer (Touch Grass panel):
 * brand strings, images, CTA links, section visibility, stats, guarantees.
 * FAQs come from tg_faq posts (ordered by the saved order field);
 * testimonials from tg_testimonial posts. When the core plugin is absent the
 * theme renders default copy so the design still previews; once the plugin
 * is active, only published content shows — deleted testimonials never
 * resurrect as fictional quotes.
 */
get_header();

$products = function_exists( 'wc_get_products' )
	? wc_get_products( [ 'limit' => (int) tg_brand( 'tg_products_per_page' ) ?: 9, 'orderby' => 'menu_order', 'order' => 'ASC', 'status' => 'publish' ] )
	: [];

/* Hero image: Customizer choice → first product image → theme default. */
$hero_image_id = (int) tg_brand( 'tg_hero_image' );
$hero_cta_primary_url = tg_brand( 'tg_hero_cta_primary_url' ) ?: tg_shop_url();
$hero_cta_secondary_url = tg_brand( 'tg_hero_cta_secondary_url' ) ?: '#how';

/* Trust row: manual override → live review data → hidden when no reviews.
 * Stars always match the number shown: the live average for live data,
 * or the merchant's manual star setting for manual text. */
$trust_rating = tg_brand( 'tg_trust_rating_text' );
$trust_avg = 5.0;
$trust_count = 0;
if ( '' === $trust_rating ) {
	$live = function_exists( 'tg_live_rating_data' ) ? tg_live_rating_data() : null;
	if ( $live ) {
		$trust_avg    = $live['avg'];
		$trust_count  = $live['count'];
		$trust_rating = tg_live_rating_text();
	}
} else {
	$trust_avg = (float) tg_brand( 'tg_trust_rating_stars' );
}

$testimonials = tg_get_testimonials();
$faqs = tg_get_faqs();
$newsletter_on = tg_brand( 'tg_section_news' ) && function_exists( 'tg_newsletter_configured' ) && tg_newsletter_configured();
$club_on = tg_brand( 'tg_section_club' ) && function_exists( 'tg_newsletter_configured' ) && tg_newsletter_configured();
?>

<section class="hero">
	<div class="wrap hero-grid">
		<div>
			<div class="eyebrow reveal"><?php echo esc_html( tg_brand( 'tg_hero_eyebrow' ) ); ?></div>
			<h1 class="reveal"><?php echo wp_kses( tg_brand( 'tg_hero_headline' ), tg_brand_kses() ); ?></h1>
			<p class="lede reveal"><?php echo esc_html( tg_brand( 'tg_hero_sub' ) ); ?></p>
			<div class="btn-row reveal">
				<a class="btn btn-primary" href="<?php echo esc_url( $hero_cta_primary_url ); ?>"><?php echo esc_html( tg_brand( 'tg_hero_cta_primary' ) ); ?></a>
				<a class="btn btn-secondary" href="<?php echo esc_url( $hero_cta_secondary_url ); ?>"><?php echo esc_html( tg_brand( 'tg_hero_cta_secondary' ) ); ?></a>
			</div>
			<div class="trust reveal">
				<?php if ( $trust_rating ) : ?>
				<span><?php echo tg_stars( $trust_avg, $trust_count ); ?> <?php echo esc_html( $trust_rating ); ?></span>
				<span class="dot" aria-hidden="true">·</span>
				<?php endif; ?>
				<span><?php echo esc_html( tg_brand( 'tg_guarantee_2_title' ) ); ?></span>
				<span class="dot" aria-hidden="true">·</span>
				<span><?php echo esc_html( tg_brand( 'tg_guarantee_1_title' ) ); ?></span>
			</div>
		</div>
		<figure class="hero-fig reveal">
			<?php
			if ( $hero_image_id ) {
				echo wp_get_attachment_image( $hero_image_id, 'large', false, [ 'alt' => get_post_meta( $hero_image_id, '_wp_attachment_image_alt', true ) ?: __( 'Touch Grass', 'touchgrass' ) ] );
			} elseif ( ! empty( $products ) ) {
				echo $products[0]->get_image( 'large' );
			} else {
				echo '<img src="' . esc_url( get_template_directory_uri() . '/assets/img/hero.webp' ) . '" alt="' . esc_attr__( 'A tray of lush green grass photographed in a studio', 'touchgrass' ) . '">';
			}
			?>
			<figcaption><?php esc_html_e( 'The Daily Driver, photographed like it matters.', 'touchgrass' ); ?></figcaption>
		</figure>
	</div>
</section>

<?php if ( tg_brand( 'tg_section_shop' ) ) : ?>
<section class="shop" id="shop" aria-labelledby="shop-title">
	<div class="wrap">
		<div class="shop-head">
			<div>
				<div class="eyebrow reveal"><?php esc_html_e( 'The lineup', 'touchgrass' ); ?></div>
				<h2 class="sec reveal" id="shop-title"><?php echo esc_html( tg_brand( 'tg_shop_title' ) ); ?></h2>
			</div>
			<p class="sec-sub reveal"><?php echo esc_html( tg_brand( 'tg_shop_sub' ) ); ?></p>
		</div>
		<?php if ( $products ) : ?>
		<div class="pgrid">
			<?php foreach ( $products as $product ) : ?>
			<article class="pcard reveal">
				<a class="pcard-link" href="<?php echo esc_url( $product->get_permalink() ); ?>">
					<div class="ph"><?php echo $product->get_image( 'woocommerce_thumbnail' ); ?></div>
					<div class="pb">
						<?php $badge = function_exists( 'tg_product_badge' ) ? tg_product_badge( $product ) : ''; ?>
						<span class="tag<?php echo $badge ? '' : ' tag-empty'; ?>"><?php echo $badge ? esc_html( $badge ) : '&nbsp;'; ?></span>
						<h3><?php echo esc_html( $product->get_name() ); ?></h3>
						<div class="sub"><?php echo esc_html( function_exists( 'tg_product_tagline' ) ? tg_product_tagline( $product ) : '' ); ?></div>
						<div class="row">
							<span class="price"><?php echo $product->get_price_html(); // phpcs:ignore ?></span>
							<?php echo tg_product_rating_html( $product ); // phpcs:ignore ?>
						</div>
					</div>
				</a>
			</article>
			<?php endforeach; ?>
		</div>
		<?php else : ?>
		<p class="sec-sub"><?php esc_html_e( 'The greenhouse is being stocked. Check back shortly.', 'touchgrass' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php endif; ?>

<?php
/* Trust strip (compact): guarantee card + fictional press + certification.
 * Reviews live further down in the proof section. */
if ( function_exists( 'tg_trust_block' ) ) {
	tg_trust_block( 'compact' );
}
?>

<?php if ( tg_brand( 'tg_section_confession' ) ) : ?>
<section class="confess" aria-labelledby="confess-title">
	<div class="wrap confess-in">
		<div class="eyebrow reveal"><?php esc_html_e( 'A moment of honesty', 'touchgrass' ); ?></div>
		<h2 class="sec reveal" id="confess-title"><?php echo esc_html( tg_brand( 'tg_confession_title' ) ); ?></h2>
		<p class="body reveal"><?php echo wp_kses_post( tg_brand( 'tg_confession_copy' ) ); ?></p>
		<div class="vs reveal" role="table" aria-label="<?php esc_attr_e( 'Outside versus Touch Grass', 'touchgrass' ); ?>">
			<div class="vs-row head" role="row"><span role="columnheader"></span><span role="columnheader"><?php esc_html_e( 'Outside', 'touchgrass' ); ?></span><span role="columnheader"><?php esc_html_e( 'Touch Grass', 'touchgrass' ); ?></span></div>
			<?php
			$rows = [
				[ __( 'Price', 'touchgrass' ), __( 'Free', 'touchgrass' ), tg_brand( 'tg_confession_price' ) ],
				[ __( 'Weather', 'touchgrass' ), __( 'Yes', 'touchgrass' ), __( 'No', 'touchgrass' ) ],
				[ __( 'Bugs', 'touchgrass' ), __( 'So many', 'touchgrass' ), __( 'Zero', 'touchgrass' ) ],
				[ __( 'Pants', 'touchgrass' ), __( 'Required', 'touchgrass' ), __( 'Optional', 'touchgrass' ) ],
				[ __( 'Other people', 'touchgrass' ), __( 'Inevitable', 'touchgrass' ), __( 'None', 'touchgrass' ) ],
				[ __( 'Effort', 'touchgrass' ), __( 'Walking', 'touchgrass' ), __( 'None', 'touchgrass' ) ],
			];
			foreach ( $rows as $r ) {
				echo '<div class="vs-row" role="row"><span class="k" role="cell">' . esc_html( $r[0] ) . '</span><span class="w" role="cell">' . esc_html( $r[1] ) . '</span><span class="u" role="cell">' . esc_html( $r[2] ) . '</span></div>';
			}
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="deed-teaser" aria-labelledby="deed-teaser-title">
	<div class="wrap">
		<div class="eyebrow reveal"><?php esc_html_e( 'The paperwork', 'touchgrass' ); ?></div>
		<h2 class="reveal" id="deed-teaser-title"><?php esc_html_e( 'Every plot ships with The Deed.', 'touchgrass' ); ?></h2>
		<p class="reveal"><?php esc_html_e( 'Provenance. Blade count. Sunlight requirements. Warranty. Recorded on heavyweight paper, stamped, sealed — and legally meaningless. The brass plaque is real, though.', 'touchgrass' ); ?></p>
		<a class="btn btn-secondary reveal" href="<?php echo esc_url( tg_shop_url() ); ?>"><?php esc_html_e( 'Inspect a plot', 'touchgrass' ); ?></a>
	</div>
</section>

<?php if ( $club_on ) : ?>
<section class="club" id="club" aria-labelledby="club-title">
	<div class="wrap">
		<div class="club-panel reveal">
			<div class="eyebrow"><?php esc_html_e( 'Membership has its privileges', 'touchgrass' ); ?></div>
			<h2 id="club-title"><?php esc_html_e( 'The Grass Club.', 'touchgrass' ); ?></h2>
			<p><?php esc_html_e( 'Members get rare (but invoiced) emails, first cut of limited batches, and absolutely no additional going outside.', 'touchgrass' ); ?></p>
			<?php tg_newsletter_form( 'club' ); ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php if ( tg_brand( 'tg_section_how' ) ) : ?>
<section class="how" id="how" aria-labelledby="how-title">
	<div class="wrap how-grid">
		<div>
			<div class="eyebrow reveal"><?php esc_html_e( 'The program', 'touchgrass' ); ?></div>
			<h2 class="sec reveal" id="how-title"><?php esc_html_e( 'Three steps.', 'touchgrass' ); ?><br><?php esc_html_e( 'Zero going outside.', 'touchgrass' ); ?></h2>
			<div class="steps">
				<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
				<div class="step reveal">
					<div class="num" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $i ) ); ?></div>
					<div>
						<h3><?php echo esc_html( tg_brand( "tg_step_{$i}_title" ) ); ?></h3>
						<p><?php echo wp_kses_post( tg_brand( "tg_step_{$i}_text" ) ); ?></p>
					</div>
				</div>
				<?php endfor; ?>
			</div>
		</div>
		<figure class="how-fig reveal">
			<?php
			$how_image_id = (int) tg_brand( 'tg_how_image' );
			if ( $how_image_id ) {
				echo wp_get_attachment_image( $how_image_id, 'large', false, [ 'alt' => get_post_meta( $how_image_id, '_wp_attachment_image_alt', true ) ?: __( 'How Touch Grass works', 'touchgrass' ), 'loading' => 'lazy' ] );
			} else {
				?>
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/hands.webp' ); ?>" alt="<?php esc_attr_e( 'Hands gently touching a tray of grass', 'touchgrass' ); ?>" loading="lazy">
				<?php
			}
			?>
		</figure>
	</div>
</section>
<?php endif; ?>

<?php if ( tg_brand( 'tg_section_proof' ) && ! empty( $testimonials ) ) : ?>
<section class="proof" id="proof" aria-labelledby="proof-title">
	<div class="wrap">
		<div class="proof-head">
			<div class="eyebrow reveal"><?php esc_html_e( 'The touched', 'touchgrass' ); ?></div>
			<h2 class="sec reveal" id="proof-title"><?php echo wp_kses( tg_brand( 'tg_proof_title' ), tg_brand_kses() ); ?></h2>
			<p class="sec-sub reveal"><?php echo esc_html( tg_brand( 'tg_proof_sub' ) ); ?></p>
		</div>
		<div class="qgrid">
			<?php foreach ( $testimonials as $t ) : ?>
			<figure class="qcard reveal">
				<?php echo tg_stars( $t[3] ); ?>
				<blockquote><?php echo wp_kses_post( $t[0] ); ?></blockquote>
				<figcaption><b><?php echo esc_html( $t[1] ); ?></b><?php echo $t[2] ? ' — ' . esc_html( $t[2] ) : ''; ?></figcaption>
			</figure>
			<?php endforeach; ?>
		</div>
		<?php
		/* Stats with empty values are hidden — no fictional defaults. The demo
		 * importer fills these in as clearly-marked demo material. */
		$stats = [];
		for ( $i = 1; $i <= 3; $i++ ) {
			$value = trim( (string) tg_brand( "tg_stat_{$i}_value" ) );
			if ( '' !== $value ) {
				$stats[] = [ $value, tg_brand( "tg_stat_{$i}_label" ) ];
			}
		}
		?>
		<?php if ( $stats ) : ?>
		<div class="stats reveal">
			<?php foreach ( $stats as $stat ) : ?>
			<div class="stat"><div class="v"><?php echo esc_html( $stat[0] ); ?></div><div class="l"><?php echo esc_html( $stat[1] ); ?></div></div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
		<figure class="desk-fig reveal">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/desk.webp' ); ?>" alt="<?php esc_attr_e( 'A Touch Grass plot on a desk next to a laptop', 'touchgrass' ); ?>" loading="lazy">
			<figcaption><?php esc_html_e( 'The Daily Driver, at work.', 'touchgrass' ); ?></figcaption>
		</figure>
	</div>
</section>
<?php endif; ?>

<?php if ( tg_brand( 'tg_section_faq' ) && ! empty( $faqs ) ) : ?>
<section class="faq" id="faq" aria-labelledby="faq-title">
	<div class="wrap faq-in">
		<div class="faq-head">
			<div class="eyebrow reveal"><?php esc_html_e( 'Objections', 'touchgrass' ); ?></div>
			<h2 class="sec reveal" id="faq-title"><?php esc_html_e( 'Overruled.', 'touchgrass' ); ?></h2>
		</div>
		<?php foreach ( $faqs as $i => $f ) : ?>
		<div class="faq-item reveal">
			<button class="faq-q" id="faq-q-<?php echo esc_attr( $i ); ?>" aria-expanded="false" aria-controls="faq-a-<?php echo esc_attr( $i ); ?>"><span><?php echo esc_html( $f[0] ); ?></span><span class="plus" aria-hidden="true">+</span></button>
			<div class="faq-a" id="faq-a-<?php echo esc_attr( $i ); ?>" role="region" aria-labelledby="faq-q-<?php echo esc_attr( $i ); ?>"><p><?php echo wp_kses_post( $f[1] ); ?></p></div>
		</div>
		<?php endforeach; ?>
	</div>
</section>
<?php
/* FAQPage structured data for the FAQ engine. */
if ( function_exists( 'tg_faq_json_ld' ) ) {
	tg_faq_json_ld();
}
?>
<?php endif; ?>

<?php if ( $newsletter_on ) : ?>
<section class="news" id="news" aria-labelledby="news-title">
	<div class="wrap">
		<div class="news-panel reveal">
			<div class="eyebrow"><?php esc_html_e( 'Field notes', 'touchgrass' ); ?></div>
			<h2 id="news-title"><?php esc_html_e( 'Join the touched.', 'touchgrass' ); ?></h2>
			<p><?php esc_html_e( 'One email a month. Occasionally about grass. Mostly about new ways to separate you from your money. Unsubscribe whenever; the grass will not take it personally.', 'touchgrass' ); ?></p>
			<?php tg_newsletter_form(); ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
get_footer();
