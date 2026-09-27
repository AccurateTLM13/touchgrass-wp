<?php /** Generic archive template. */ get_header(); ?>
<div class="wrap page-hero">
	<div class="eyebrow"><?php _e( 'Archive', 'touchgrass' ); ?></div>
	<h1><?php the_archive_title(); ?></h1>
	<?php the_archive_description( '<p class="lede">', '</p>' ); ?>
</div>
<div class="wrap page-body">
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<article class="tg-archive-item">
			<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<?php the_excerpt(); ?>
		</article>
	<?php endwhile; the_posts_pagination(); else : ?>
		<p><?php _e( 'Nothing here. The grass is empty.', 'touchgrass' ); ?></p>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
