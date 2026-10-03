</main>

<footer class="site-foot">
	<div class="foot-in">
		<div class="foot-grid">
			<div>
				<div class="foot-word"><?php tg_wordmark(); ?></div>
				<p class="foot-tag"><?php echo esc_html( tg_brand( 'tg_footer_tagline' ) ); ?></p>
			</div>
			<div>
				<div class="foot-h"><?php esc_html_e( 'Shop', 'touchgrass' ); ?></div>
				<?php
				if ( has_nav_menu( 'foot_shop' ) ) {
					wp_nav_menu( [ 'theme_location' => 'foot_shop', 'container' => false, 'items_wrap' => '<ul class="foot-list">%3$s</ul>', 'depth' => 1 ] );
				} else {
					?>
					<ul class="foot-list"><li><a href="<?php echo esc_url( tg_shop_url() ); ?>"><?php esc_html_e( 'All products', 'touchgrass' ); ?></a></li></ul>
					<?php
				}
				?>
			</div>
			<div>
				<div class="foot-h"><?php esc_html_e( 'Company', 'touchgrass' ); ?></div>
				<?php
				if ( has_nav_menu( 'foot_company' ) ) {
					wp_nav_menu( [ 'theme_location' => 'foot_company', 'container' => false, 'items_wrap' => '<ul class="foot-list">%3$s</ul>', 'depth' => 1 ] );
				} else {
					?>
					<ul class="foot-list">
						<li><a href="<?php echo esc_url( home_url( '/#how' ) ); ?>"><?php esc_html_e( 'Why grass?', 'touchgrass' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/#proof' ) ); ?>"><?php esc_html_e( 'Reviews', 'touchgrass' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/#faq' ) ); ?>"><?php esc_html_e( 'FAQ', 'touchgrass' ); ?></a></li>
					</ul>
					<?php
				}
				?>
			</div>
			<div>
				<div class="foot-h"><?php esc_html_e( 'Support', 'touchgrass' ); ?></div>
				<?php
				if ( has_nav_menu( 'foot_support' ) ) {
					wp_nav_menu( [ 'theme_location' => 'foot_support', 'container' => false, 'items_wrap' => '<ul class="foot-list">%3$s</ul>', 'depth' => 1 ] );
				} else {
					?>
					<ul class="foot-list">
						<li><a href="<?php echo esc_url( home_url( '/#faq' ) ); ?>"><?php esc_html_e( 'Shipping info', 'touchgrass' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/#faq' ) ); ?>"><?php esc_html_e( 'Returns', 'touchgrass' ); ?></a></li>
						<?php if ( tg_woo_active() ) : ?>
						<li><a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'Cart', 'touchgrass' ); ?></a></li>
						<?php endif; ?>
					</ul>
					<?php
				}
				?>
			</div>
		</div>
		<div class="foot-base">
			<span><?php echo esc_html( sprintf( __( '© %1$s %2$s. %3$s', 'touchgrass' ), date_i18n( 'Y' ), get_bloginfo( 'name' ), tg_brand( 'tg_footer_credit' ) ) ); ?></span>
			<span><?php echo esc_html( tg_brand( 'tg_footer_signoff' ) ); ?></span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
