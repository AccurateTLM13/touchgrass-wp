<?php /** Search results template. */ get_header(); ?>
<div class="wrap page-hero">
	<div class="eyebrow"><?php _e( 'Search', 'touchgrass' ); ?></div>
	<h1><?php printf( __( 'Results for &ldquo;%s&rdquo;', 'touchgrass' ), esc_html( get_search_query() ) ); ?></h1>
</div>
<div class="wrap page-body">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article class="tg-archive-item">
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<?php the_excerpt(); ?>
		</article>
	<?php endwhile; the_posts_pagination(); else : ?>
		<p><?php _e( 'No results. Try "grass".', 'touchgrass' ); ?></p>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
