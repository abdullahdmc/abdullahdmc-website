<?php
/**
 * Search results (posts only).
 */
defined( 'ABSPATH' ) || exit;
amk_head();
amk_header( 'writing' );
?>
<section class="wrap page-intro">
	<?php amk_crumb( 'Search', array( 'Writing' => amk_writing_url() ) ); ?>
	<div class="intro-line"><div>
		<h1>Search: “<?php echo esc_html( get_search_query() ); ?>”</h1>
		<p class="lead"><?php echo esc_html( sprintf( 1 === (int) $wp_query->found_posts ? '%s result' : '%s results', number_format( (int) $wp_query->found_posts ) ) ); ?> in the writing and AI research roundups.</p>
	</div></div>
</section>
<section class="wrap archive-section" id="archive">
	<form class="archive-controls" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<div class="field"><label for="archive-search">Search the writing</label><input type="search" id="archive-search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="বাংলা বা English…" autocomplete="off"></div>
		<button class="button outline-dark archive-submit" type="submit">Search</button>
	</form>
	<?php amk_grid(); ?>
</section>
<?php
amk_footer();
