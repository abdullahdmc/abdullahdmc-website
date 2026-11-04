<?php
/**
 * Writing page: essays by default, filterable by format (?theme=ai&kind=..., ?show=all) and by
 * theme (?theme=science). The newest essay leads the first page.
 */
defined( 'ABSPATH' ) || exit;

$amk_theme = isset( $_GET['theme'] ) ? sanitize_key( wp_unslash( $_GET['theme'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$amk_kind  = isset( $_GET['kind'] ) ? sanitize_key( wp_unslash( $_GET['kind'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$amk_t     = amk_themes()[ $amk_theme ] ?? null;
$amk_show  = amk_writing_show();
$amk_paged = max( 1, (int) get_query_var( 'paged' ) );
// Format tabs: essays (default), roundups, podcast, everything.
$amk_format = 'all' === $amk_show ? 'all' : ( 'ai' === $amk_theme ? ( 'podcasts' === $amk_kind ? 'podcast' : 'roundup' ) : 'article' );
$amk_lead   = ! $amk_t && ! $amk_show && 1 === $amk_paged && have_posts();
$amk_ai_ids  = amk_theme_category_ids( 'ai' );
$amk_pod_tag = get_term_by( 'slug', 'podcast', 'post_tag' );
$amk_counts  = array(
	'essays'   => ( new WP_Query( array( 'category__not_in' => $amk_ai_ids, 'posts_per_page' => 1, 'fields' => 'ids' ) ) )->found_posts,
	'roundups' => $amk_ai_ids ? ( new WP_Query( array( 'category__in' => $amk_ai_ids, 'tag__not_in' => $amk_pod_tag ? array( $amk_pod_tag->term_id ) : array(), 'posts_per_page' => 1, 'fields' => 'ids' ) ) )->found_posts : 0,
	'podcasts' => $amk_pod_tag ? (int) $amk_pod_tag->count : 0,
);

amk_head();
amk_header( 'writing' );
?>
<section class="wrap page-intro">
	<?php amk_crumb( $amk_t ? $amk_t['bn'] : 'Writing', $amk_t ? array( 'Writing' => amk_writing_url() ) : array() ); ?>
	<div class="intro-line"><div>
		<h1<?php echo ! $amk_t || amk_is_bangla( $amk_t['bn'] ) ? ' lang="bn"' : ''; ?>><?php echo esc_html( $amk_t ? $amk_t['bn'] : 'লেখা ও ভাবনা' ); ?></h1>
		<p class="lead"><?php echo esc_html( $amk_t ? $amk_t['en'] : 'Science, philosophy, theology, research and life, in both Bangla and English.' ); ?></p>
		<p class="demo-note"><?php echo esc_html( implode( ' · ', array_filter( array(
			number_format( $amk_counts['essays'] ) . ' essays, poems and reviews',
			$amk_counts['roundups'] ? number_format( $amk_counts['roundups'] ) . ' AI research roundups' : '',
			$amk_counts['podcasts'] ? number_format( $amk_counts['podcasts'] ) . ( 1 === $amk_counts['podcasts'] ? ' podcast episode' : ' podcast episodes' ) : '',
		) ) ) ); ?></p>
	</div>
	<?php
	// Header picture: the study for writing in general, light through glass for faith and philosophy.
	if ( ! $amk_t ) {
		echo amk_intro_art( 'books', 'A quiet study with open notebook and books, overlooking Oxford' ); // phpcs:ignore WordPress.Security.EscapeOutput
	} elseif ( 'faith' === $amk_theme ) {
		echo amk_intro_art( 'reason', 'Light refracted through glass onto stone walls' ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	?>
	</div>
</section>

<?php if ( $amk_lead ) : the_post(); $amk_url = get_permalink(); $amk_bn = amk_post_is_bangla(); $amk_lang = $amk_bn ? ' lang="bn"' : ''; ?>
<section class="wrap featured-essay">
	<a href="<?php echo esc_url( $amk_url ); ?>" class="feature-cover" tabindex="-1" aria-hidden="true"><?php echo amk_post_image( get_post(), 'amk-cover', '(max-width:800px) calc(100vw - 40px), 600px', 'eager' ); // phpcs:ignore ?></a>
	<div>
		<p class="meta"<?php echo $amk_lang; // phpcs:ignore ?>><?php echo esc_html( amk_themes()[ amk_theme_of() ]['bn'] . ' · ' . amk_date() ); ?></p>
		<h2<?php echo $amk_lang; // phpcs:ignore ?>><a href="<?php echo esc_url( $amk_url ); ?>"><?php the_title(); ?></a></h2>
		<p<?php echo $amk_lang; // phpcs:ignore ?>><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
		<a class="text-link" href="<?php echo esc_url( $amk_url ); ?>"<?php echo $amk_lang; // phpcs:ignore ?>><?php echo $amk_bn ? 'সম্পূর্ণ লেখা পড়ুন' : 'Read article'; ?> →</a>
	</div>
</section>
<?php rewind_posts(); endif; ?>

<section class="wrap archive-section" id="archive">
	<form class="archive-controls" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<div class="field"><label for="archive-search">Search the writing</label><input type="search" id="archive-search" name="s" placeholder="বাংলা বা English…" autocomplete="off"></div>
		<button class="button outline-dark archive-submit" type="submit">Search</button>
	</form>
	<nav class="filter-tabs format-tabs" aria-label="Filter by format">
		<?php
		$amk_tabs = array(
			'article' => array( 'Essays', amk_writing_url() ),
			'roundup' => array( 'AI roundups', amk_writing_url( array( 'theme' => 'ai', 'kind' => 'roundups' ) ) ),
			'podcast' => array( 'Podcast', amk_writing_url( array( 'theme' => 'ai', 'kind' => 'podcasts' ) ) ),
			'all'     => array( 'Everything', amk_writing_url( array( 'show' => 'all' ) ) ),
		);
		foreach ( $amk_tabs as $amk_key => $amk_tab ) :
			if ( 'podcast' === $amk_key && ! $amk_counts['podcasts'] ) {
				continue;
			}
			?>
			<a href="<?php echo esc_url( $amk_tab[1] ); ?>"<?php echo $amk_key === $amk_format ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $amk_tab[0] ); ?></a>
		<?php endforeach; ?>
	</nav>
	<?php
	$amk_notes = array(
		'article' => 'Long-form essays, poems and reviews, mostly in Bangla.',
		'roundup' => 'Short daily summaries of new studies on AI in health research, in English.',
		'podcast' => 'Audio episodes on AI in health research.',
		'all'     => 'Essays, roundups and podcast episodes together.',
	);
	?>
	<p class="format-note"><?php echo esc_html( $amk_notes[ $amk_format ] ); ?></p>
	<?php if ( 'article' === $amk_format ) : ?>
	<nav class="filter-tabs" aria-label="Filter by topic">
		<a href="<?php echo esc_url( amk_writing_url() ); ?>"<?php echo $amk_t ? '' : ' aria-current="page"'; ?>>All writing</a>
		<?php foreach ( amk_themes() as $amk_key => $amk_th ) : ?>
			<?php
			if ( 'ai' === $amk_key ) {
				continue;
			}
			$amk_n = 0;
			foreach ( amk_theme_category_ids( $amk_key ) as $amk_id ) {
				$amk_term = get_term( $amk_id, 'category' );
				$amk_n   += ( $amk_term && ! is_wp_error( $amk_term ) ) ? (int) $amk_term->count : 0;
			}
			if ( ! $amk_n ) {
				continue;
			}
			?>
			<a href="<?php echo esc_url( amk_writing_url( array( 'theme' => $amk_key ) ) ); ?>" lang="bn"<?php echo $amk_key === $amk_theme ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $amk_th['bn'] ); ?></a>
		<?php endforeach; ?>
	</nav>
	<?php endif; ?>
	<div class="archive-result-line">
		<p><?php echo esc_html( sprintf( 1 === (int) $wp_query->found_posts ? '%s entry' : '%s entries', number_format( (int) $wp_query->found_posts ) ) ); ?><?php echo $amk_paged > 1 ? esc_html( sprintf( ' · page %d of %d', $amk_paged, (int) $wp_query->max_num_pages ) ) : ''; ?></p>
		<?php if ( $amk_t || $amk_show ) : ?><a class="plain-button" href="<?php echo esc_url( amk_writing_url() ); ?>">Clear filters</a><?php endif; ?>
	</div>
	<?php amk_grid( $amk_lead ? 1 : 0 ); ?>
</section>
<?php
amk_footer();
