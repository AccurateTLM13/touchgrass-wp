<?php
/** Static page template. */
get_header();
while ( have_posts() ) :
	the_post();
?>
<div class="wrap page-hero">
	<h1><?php the_title(); ?></h1>
</div>
<div class="wrap page-body">
	<?php the_content(); ?>
</div>
<?php
endwhile;
get_footer();
