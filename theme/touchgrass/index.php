<?php
/** Fallback template. */
get_header();
?>
<div class="wrap page-hero">
	<h1><?php echo esc_html( get_the_title() ? get_the_title() : get_bloginfo( 'name' ) ); ?></h1>
</div>
<div class="wrap page-body">
<?php
while ( have_posts() ) :
	the_post();
	the_content();
endwhile;
?>
</div>
<?php
get_footer();
