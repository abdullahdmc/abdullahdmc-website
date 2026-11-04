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
	<?php if ( 'ai' === $amk_theme ) : $amk_kind = isset( $_GET['kind'] ) ? sanitize_key( wp_unslash( $_GET['kind'] ) ) : ''; ?>
		<div class="chips kind-chips">
			<?php foreach ( array( '' => 'All', 'roundups' => 'Roundups', 'podcasts' => 'Podcasts' ) as $amk_k => $amk_l ) : ?>
				<a class="chip" aria-pressed="<?php echo $amk_k === $amk_kind ? 'true' : 'false'; ?>" href="<?php echo esc_url( amk_writing_url( array_filter( array( 'theme' => 'ai', 'kind' => $amk_k ) ) ) ); ?>"><?php echo 'podcasts' === $amk_k ? amk_headphones_icon() : ''; // phpcs:ignore ?><?php echo esc_html( $amk_l ); ?></a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php amk_post_grid(); ?>
</div></section>
<?php
amk_footer();
