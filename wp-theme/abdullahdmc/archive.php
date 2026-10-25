<?php
/**
 * Category, tag, author and date archives.
 */
defined( 'ABSPATH' ) || exit;
amk_head();
amk_header( 'writing' );
?>
<section class="page-head"><div class="wrap">
	<span class="eyebrow">লেখা · Writing</span>
	<h1><?php echo esc_html( wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
	<?php the_archive_description( '<p>', '</p>' ); ?>
</div></section>
<section><div class="wrap">
	<?php amk_post_grid(); ?>
</div></section>
<?php
amk_footer();
