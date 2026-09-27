<?php
/**
 * Front page: hero, confession, shop, how it works, reviews, FAQ, newsletter.
 * All business copy is editable: Customizer (Touch Grass section) for brand
 * strings, tg_faq / tg_testimonial posts for FAQ and reviews.
 * Falls back to default copy when the core plugin isn't active.
 */
get_header();

$products = function_exists( 'wc_get_products' )
	? wc_get_products( [ 'limit' => 9, 'orderby' => 'menu_order', 'order' => 'ASC', 'status' => 'publish' ] )
	: [];
?>

<section class="hero">
	<div class="wrap hero-grid">
		<div>
			<div class="eyebrow reveal"><?php echo esc_html( tg_brand( 'tg_hero_eyebrow' ) ); ?></div>
			<h1 class="reveal"><?php echo wp_kses( tg_brand( 'tg_hero_headline' ), tg_brand_kses() ); ?></h1>
			<p class="lede reveal"><?php echo esc_html( tg_brand( 'tg_hero_sub' ) ); ?></p>
			<div class="btn-row reveal">
				<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"><?php echo esc_html( tg_brand( 'tg_hero_cta_primary' ) ); ?></a>
				<a class="btn btn-secondary" href="#how"><?php echo esc_html( tg_brand( 'tg_hero_cta_secondary' ) ); ?></a>
			</div>
			<div class="trust reveal">
				<span><?php echo tg_stars( 5 ); ?> <?php esc_html_e( '4.9 from 8,600+ reviews', 'touchgrass' ); ?></span>
				<span class="dot">·</span>
				<span><?php echo esc_html( tg_brand( 'tg_guarantee_2_title' ) ); ?></span>
				<span class="dot">·</span>
				<span><?php echo esc_html( tg_brand( 'tg_guarantee_1_title' ) ); ?></span>
			</div>
		</div>
		<figure class="hero-fig reveal">
			<?php
			$hero_img = get_template_directory_uri() . '/assets/img/hero.webp';
			if ( ! empty( $products ) ) {
				echo $products[0]->get_image( 'large' );
			} else {
				echo '<img src="' . esc_url( $hero_img ) . '" alt="' . esc_attr__( 'A tray of lush green grass photographed in a studio', 'touchgrass' ) . '">';
			}
			?>
			<figcaption><?php esc_html_e( 'The Daily Driver, photographed like it matters.', 'touchgrass' ); ?></figcaption>
		</figure>
	</div>
</section>

<section class="confess">
	<div class="wrap confess-in">
		<div class="eyebrow reveal"><?php esc_html_e( 'A moment of honesty', 'touchgrass' ); ?></div>
		<h2 class="sec reveal"><?php echo esc_html( tg_brand( 'tg_confession_title' ) ); ?></h2>
		<p class="body reveal"><?php echo wp_kses_post( tg_brand( 'tg_confession_copy' ) ); ?></p>
		<div class="vs reveal">
			<div class="vs-row head"><span></span><span><?php esc_html_e( 'Outside', 'touchgrass' ); ?></span><span><?php esc_html_e( 'Touch Grass', 'touchgrass' ); ?></span></div>
			<?php
			$rows = [
				[ __( 'Price', 'touchgrass' ), __( 'Free', 'touchgrass' ), '$29' ],
				[ __( 'Weather', 'touchgrass' ), __( 'Yes', 'touchgrass' ), __( 'No', 'touchgrass' ) ],
				[ __( 'Bugs', 'touchgrass' ), __( 'So many', 'touchgrass' ), __( 'Zero', 'touchgrass' ) ],
				[ __( 'Pants', 'touchgrass' ), __( 'Required', 'touchgrass' ), __( 'Optional', 'touchgrass' ) ],
				[ __( 'Other people', 'touchgrass' ), __( 'Inevitable', 'touchgrass' ), __( 'None', 'touchgrass' ) ],
				[ __( 'Effort', 'touchgrass' ), __( 'Walking', 'touchgrass' ), __( 'None', 'touchgrass' ) ],
			];
			foreach ( $rows as $r ) {
				echo '<div class="vs-row"><span class="k">' . esc_html( $r[0] ) . '</span><span class="w">' . esc_html( $r[1] ) . '</span><span class="u">' . esc_html( $r[2] ) . '</span></div>';
			}
			?>
		</div>
	</div>
</section>

<section class="shop" id="shop">
	<div class="wrap">
		<div class="shop-head">
			<div>
				<div class="eyebrow reveal"><?php esc_html_e( 'The lineup', 'touchgrass' ); ?></div>
				<h2 class="sec reveal"><?php esc_html_e( 'Nine rectangles* of grass.', 'touchgrass' ); ?></h2>
			</div>
			<p class="sec-sub reveal"><?php esc_html_e( '*Two are circles. Two are not grass. We counted them anyway.', 'touchgrass' ); ?></p>
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
							<?php echo tg_stars( 5 ); ?>
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

<section class="how" id="how">
	<div class="wrap how-grid">
		<div>
			<div class="eyebrow reveal"><?php esc_html_e( 'The program', 'touchgrass' ); ?></div>
			<h2 class="sec reveal"><?php esc_html_e( 'Three steps.', 'touchgrass' ); ?><br><?php esc_html_e( 'Zero going outside.', 'touchgrass' ); ?></h2>
			<div class="steps">
				<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
				<div class="step reveal">
					<div class="num"><?php echo esc_html( sprintf( '%02d', $i ) ); ?></div>
					<div>
						<h3><?php echo esc_html( tg_brand( "tg_step_{$i}_title" ) ); ?></h3>
						<p><?php echo wp_kses_post( tg_brand( "tg_step_{$i}_text" ) ); ?></p>
					</div>
				</div>
				<?php endfor; ?>
			</div>
		</div>
		<figure class="how-fig reveal">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/hands.webp' ); ?>" alt="<?php esc_attr_e( 'Hands gently touching a tray of grass', 'touchgrass' ); ?>" loading="lazy">
		</figure>
	</div>
</section>

<section class="proof" id="proof">
	<div class="wrap">
		<div class="proof-head">
			<div class="eyebrow reveal"><?php esc_html_e( 'The touched', 'touchgrass' ); ?></div>
			<h2 class="sec reveal"><?php esc_html_e( '8,600 indoor humans.', 'touchgrass' ); ?><br><?php esc_html_e( 'Zero walks taken.', 'touchgrass' ); ?></h2>
			<p class="sec-sub reveal"><?php esc_html_e( 'Every review from someone who chose $29 over free.', 'touchgrass' ); ?></p>
		</div>
		<div class="qgrid">
			<?php foreach ( tg_get_testimonials() as $t ) : ?>
			<figure class="qcard reveal">
				<?php echo tg_stars( $t[3] ); ?>
				<blockquote><?php echo wp_kses_post( $t[0] ); ?></blockquote>
				<figcaption><b><?php echo esc_html( $t[1] ); ?></b><?php echo $t[2] ? ' — ' . esc_html( $t[2] ) : ''; ?></figcaption>
			</figure>
			<?php endforeach; ?>
		</div>
		<div class="stats reveal">
			<div class="stat"><div class="v">8,600+</div><div class="l"><?php esc_html_e( 'verified reviews', 'touchgrass' ); ?></div></div>
			<div class="stat"><div class="v">4.9</div><div class="l"><?php esc_html_e( 'average rating', 'touchgrass' ); ?></div></div>
			<div class="stat"><div class="v">0</div><div class="l"><?php esc_html_e( 'walks taken', 'touchgrass' ); ?></div></div>
		</div>
		<figure class="desk-fig reveal">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/desk.webp' ); ?>" alt="<?php esc_attr_e( 'A Touch Grass plot on a desk next to a laptop', 'touchgrass' ); ?>" loading="lazy">
			<figcaption><?php esc_html_e( 'The Daily Driver, at work.', 'touchgrass' ); ?></figcaption>
		</figure>
	</div>
</section>

<section class="faq" id="faq">
	<div class="wrap faq-in">
		<div class="faq-head">
			<div class="eyebrow reveal"><?php esc_html_e( 'Objections', 'touchgrass' ); ?></div>
			<h2 class="sec reveal"><?php esc_html_e( 'Overruled.', 'touchgrass' ); ?></h2>
		</div>
		<?php foreach ( tg_get_faqs() as $f ) : ?>
		<div class="faq-item reveal">
			<button class="faq-q" aria-expanded="false"><span><?php echo esc_html( $f[0] ); ?></span><span class="plus">+</span></button>
			<div class="faq-a"><p><?php echo wp_kses_post( $f[1] ); ?></p></div>
		</div>
		<?php endforeach; ?>
	</div>
</section>

<section class="news" id="news">
	<div class="wrap">
		<div class="news-panel reveal">
			<div class="eyebrow"><?php esc_html_e( 'Field notes', 'touchgrass' ); ?></div>
			<h2><?php esc_html_e( 'Join the touched.', 'touchgrass' ); ?></h2>
			<p><?php esc_html_e( 'One email a month. Occasionally about grass. Mostly about new ways to separate you from your money. Unsubscribe whenever; the grass will not take it personally.', 'touchgrass' ); ?></p>
			<form class="news-form" id="newsForm" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
				<?php wp_nonce_field( 'tg_newsletter', 'tg_newsletter_nonce' ); ?>
				<input type="email" id="newsEmail" name="email" placeholder="you@indoors.dev" aria-label="<?php esc_attr_e( 'Email address', 'touchgrass' ); ?>" required>
				<button type="submit"><?php esc_html_e( 'Subscribe', 'touchgrass' ); ?></button>
			</form>
			<div class="news-fine" id="newsFine"><?php esc_html_e( 'No spam. The grass insists.', 'touchgrass' ); ?></div>
			<div class="news-done" id="newsDone" role="status"></div>
			<div class="news-error" id="newsError" role="alert"></div>
		</div>
	</div>
</section>

<?php
get_footer();
