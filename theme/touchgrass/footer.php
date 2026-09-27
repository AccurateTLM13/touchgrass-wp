</main>

<footer class="site-foot">
	<div class="foot-in">
		<div class="foot-grid">
			<div>
				<div class="foot-word">Touch <em>Grass</em></div>
				<p class="foot-tag"><?php echo esc_html( tg_brand( 'tg_footer_tagline' ) ); ?></p>
			</div>
			<div>
				<div class="foot-h"><?php esc_html_e( 'Shop', 'touchgrass' ); ?></div>
				<?php
				if ( has_nav_menu( 'foot_shop' ) ) {
					wp_nav_menu( [ 'theme_location' => 'foot_shop', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1 ] );
				} else {
					?>
					<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'All products', 'touchgrass' ); ?></a>
					<?php
				}
				?>
			</div>
			<div>
				<div class="foot-h"><?php esc_html_e( 'Company', 'touchgrass' ); ?></div>
				<?php
				if ( has_nav_menu( 'foot_company' ) ) {
					wp_nav_menu( [ 'theme_location' => 'foot_company', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1 ] );
				} else {
					?>
					<a href="<?php echo esc_url( home_url( '/#how' ) ); ?>"><?php esc_html_e( 'Why grass?', 'touchgrass' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/#proof' ) ); ?>"><?php esc_html_e( 'Reviews', 'touchgrass' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/#faq' ) ); ?>"><?php esc_html_e( 'FAQ', 'touchgrass' ); ?></a>
					<?php
				}
				?>
			</div>
			<div>
				<div class="foot-h"><?php esc_html_e( 'Support', 'touchgrass' ); ?></div>
				<?php
				if ( has_nav_menu( 'foot_support' ) ) {
					wp_nav_menu( [ 'theme_location' => 'foot_support', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1 ] );
				} else {
					?>
					<a href="<?php echo esc_url( home_url( '/#faq' ) ); ?>"><?php esc_html_e( 'Shipping info', 'touchgrass' ); ?></a>
					<a href="<?php echo esc_url( home_url( '/#faq' ) ); ?>"><?php esc_html_e( 'Returns', 'touchgrass' ); ?></a>
					<?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
					<a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'Cart', 'touchgrass' ); ?></a>
					<?php endif; ?>
					<?php
				}
				?>
			</div>
		</div>
		<div class="foot-base">
			<span><?php esc_html_e( '© 2026 Touch Grass Inc. Made with chlorophyll.', 'touchgrass' ); ?></span>
			<span><?php esc_html_e( 'Going outside is still free.', 'touchgrass' ); ?></span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
