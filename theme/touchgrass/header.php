<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<script>document.documentElement.classList.add('js');</script>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'touchgrass' ); ?></a>

<?php if ( tg_brand( 'tg_announcement_show' ) ) : ?>
<div class="announce"><?php echo wp_kses( tg_brand( 'tg_announcement_text' ), tg_brand_kses() ); ?></div>
<?php endif; ?>

<header class="site-head">
	<div class="head-in">
		<?php tg_wordmark(); ?>
		<nav class="nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'touchgrass' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( [
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'nav-list',
					'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
					'depth'          => 1,
				] );
			} else {
				/* Fallback links until a menu is assigned. Shop URL comes from
				 * the configured WooCommerce shop page, never assumed. */
				?>
				<ul class="nav-list">
					<li><a href="<?php echo esc_url( tg_shop_url() ); ?>"><?php esc_html_e( 'Shop', 'touchgrass' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/#how' ) ); ?>"><?php esc_html_e( 'Why grass?', 'touchgrass' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/#proof' ) ); ?>"><?php esc_html_e( 'Reviews', 'touchgrass' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/#faq' ) ); ?>"><?php esc_html_e( 'FAQ', 'touchgrass' ); ?></a></li>
				</ul>
				<?php
			}
			?>
		</nav>
		<button class="nav-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="<?php esc_attr_e( 'Menu', 'touchgrass' ); ?>">
			<span></span><span></span><span></span>
		</button>
		<?php if ( tg_woo_active() ) : ?>
		<a class="cart-btn" href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ) ); ?>">
			<?php tg_cart_button_inner(); ?>
		</a>
		<?php endif; ?>
	</div>
</header>

<main class="site-main" id="main">
