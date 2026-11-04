<?php
/**
 * Ordinary pages (for example the old Publications and Sitemap pages).
 */
defined( 'ABSPATH' ) || exit;
amk_head();
amk_header();
while ( have_posts() ) :
	the_post();
	?>
	<section class="page-head"><div class="wrap"><h1><?php the_title(); ?></h1></div></section>
	<section><div class="wrap"><div class="prose page-prose<?php echo amk_is_bangla( get_the_title() ) ? '' : ' en'; ?>"><?php the_content(); ?></div></div></section>
	<?php
endwhile;
amk_footer();
