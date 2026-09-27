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

<div class="announce"><?php echo wp_kses( tg_brand( 'tg_announcement_text' ), tg_brand_kses() ); ?></div>

<header class="site-head">
	<div class="head-in">
		<a class="wordmark" href="<?php echo esc_url( home_url( '/' ) ); ?>">Touch <em>Grass</em></a>
		<nav class="nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'touchgrass' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( [
					'theme_location' => 'primary',
					'container'      => false,
					'items_wrap'     => '%3$s',
					'depth'          => 1,
				] );
			} else {
				/* Fallback links until the demo importer (or the user) builds the menu. */
				?>
				<a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'Shop', 'touchgrass' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/#how' ) ); ?>"><?php esc_html_e( 'Why grass?', 'touchgrass' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/#proof' ) ); ?>"><?php esc_html_e( 'Reviews', 'touchgrass' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/#faq' ) ); ?>"><?php esc_html_e( 'FAQ', 'touchgrass' ); ?></a>
				<?php
			}
			?>
		</nav>
		<button class="nav-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="Menu">
			<span></span><span></span><span></span>
		</button>
		<a class="cart-btn" href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ) ); ?>">
			<?php tg_cart_button_inner(); ?>
		</a>
	</div>
</header>

<main class="site-main">
