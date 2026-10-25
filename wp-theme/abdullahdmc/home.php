<?php
/**
 * Writing page: all posts, newest first, filterable by theme (?theme=) with search.
 */
defined( 'ABSPATH' ) || exit;
$amk_theme = isset( $_GET['theme'] ) ? sanitize_key( wp_unslash( $_GET['theme'] ) ) : '';
$amk_t     = amk_themes()[ $amk_theme ] ?? null;
amk_head();
amk_header( 'writing' );
?>
<section class="page-head"><div class="wrap">
	<span class="eyebrow">লেখা · Writing</span>
	<h1><?php echo $amk_t ? esc_html( $amk_t['bn'] ) : 'প্রবন্ধ, কবিতা ও ভাবনা'; ?></h1>
	<p>বিবর্তন, মস্তিষ্ক, চিকিৎসা, গবেষণা, বিশ্বাস ও দর্শন নিয়ে লেখা, সঙ্গে কবিতা, গল্প আর বইয়ের রিভিউ।</p>
</div></section>
<section><div class="wrap">
	<div class="toolbar">
		<?php amk_theme_chips( $amk_theme ); ?>
		<form class="search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
			<input type="search" name="s" placeholder="লেখা খুঁজুন…" aria-label="Search posts">
		</form>
	</div>
	<?php amk_post_grid(); ?>
</div></section>
<?php
amk_footer();
