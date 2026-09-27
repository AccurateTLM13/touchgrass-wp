<?php /** 404 — the page went outside and never came back. */ get_header(); ?>
<div class="wrap page-hero">
	<div class="eyebrow"><?php _e( 'Lost in the lawn', 'touchgrass' ); ?></div>
	<h1><?php _e( 'This page went outside.', 'touchgrass' ); ?></h1>
	<p class="lede"><?php _e( 'It never came back. Unlike our grass, which stays exactly where you put it.', 'touchgrass' ); ?></p>
	<p><a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( 'Back to safety', 'touchgrass' ); ?></a></p>
</div>
<?php get_footer(); ?>
