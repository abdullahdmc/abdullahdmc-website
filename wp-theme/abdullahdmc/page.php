<?php
/**
 * Ordinary pages (for example the old Publications and Sitemap pages).
 */
defined( 'ABSPATH' ) || exit;
amk_head();
amk_header();
while ( have_posts() ) :
	the_post();
	$amk_bn = amk_post_is_bangla();
	?>
	<div class="reading-progress" aria-hidden="true"><span></span></div>
	<section class="wrap page-intro">
		<?php amk_crumb( wp_strip_all_tags( get_the_title() ) ); ?>
		<div class="intro-line"><div><h1<?php echo $amk_bn ? ' lang="bn"' : ''; ?>><?php the_title(); ?></h1></div></div>
	</section>
	<div class="wrap page-layout">
		<article <?php post_class( 'article-body page-body' ); ?><?php echo $amk_bn ? ' lang="bn"' : ''; ?>>
			<?php the_content(); ?>
			<?php wp_link_pages( array( 'before' => '<nav class="page-links" aria-label="Pages">', 'after' => '</nav>' ) ); ?>
		</article>
	</div>
	<?php
endwhile;
amk_footer();
