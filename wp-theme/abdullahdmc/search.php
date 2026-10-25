<?php
/**
 * Search results.
 */
defined( 'ABSPATH' ) || exit;
amk_head();
amk_header( 'writing' );
?>
<section class="page-head"><div class="wrap">
	<span class="eyebrow">খোঁজ · Search</span>
	<h1>“<?php echo esc_html( get_search_query() ); ?>”</h1>
	<form class="search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" style="margin-top:16px">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
		<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" aria-label="Search posts">
	</form>
</div></section>
<section><div class="wrap">
	<?php amk_post_grid(); ?>
</div></section>
<?php
amk_footer();
