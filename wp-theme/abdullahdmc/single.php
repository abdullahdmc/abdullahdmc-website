<?php
/**
 * A single post: title, byline, contents list, text, series links, sharing, author note and
 * related reading. Labels follow the post's language.
 */
defined( 'ABSPATH' ) || exit;
amk_head();
amk_header( 'writing' );
while ( have_posts() ) :
	the_post();
	$amk_key    = amk_theme_of();
	$amk_theme  = amk_themes()[ $amk_key ];
	$amk_bangla = amk_post_is_bangla();
	$amk_w      = amk_words( $amk_bangla );
	$amk_pod    = amk_podcast();
	$amk_series = amk_series_ids();
	$amk_part   = amk_series_part();
	$amk_url    = get_permalink();
	list( $amk_html, $amk_toc ) = amk_article_content();
	$amk_n      = function ( $n ) use ( $amk_bangla ) {
		return $amk_bangla ? amk_bn_digits( $n ) : (string) $n;
	};
	?>
	<div class="reading-progress" aria-hidden="true"><span></span></div>
	<section class="wrap article-heading">
		<?php amk_crumb( $amk_theme['bn'], array( 'Writing' => amk_writing_url() ) ); ?>
		<div class="article-title-block">
			<p class="meta"><time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( amk_date() ); ?></time><span><?php echo esc_html( $amk_pod ? 'Podcast' . ( $amk_pod[1] ? ' · ' . $amk_pod[1] : '' ) : sprintf( $amk_w['minutes'], $amk_n( amk_reading_minutes() ) ) ); ?></span><?php if ( $amk_part ) : ?><a class="series-tag" href="<?php echo esc_url( amk_series_url() ); ?>" lang="bn">ধারাবাহিক · পর্ব <?php echo esc_html( amk_bn_digits( $amk_part ) . ' / ' . amk_bn_digits( count( $amk_series ) ) ); ?></a><?php endif; ?></p>
			<h1><?php the_title(); ?></h1>
			<div class="byline"><img src="<?php echo esc_url( amk_img( 'portrait-128.webp' ) ); ?>" alt="" width="42" height="42"><div><a href="<?php echo esc_url( amk_page_url( 'biography' ) ); ?>">Dr. Md Abdullah Saeed Khan</a><span><?php echo esc_html( $amk_w['role'] ); ?></span></div></div>
		</div>
	</section>
	<div class="wrap article-layout">
		<aside class="article-sidebar<?php echo count( $amk_toc ) > 1 ? '' : ' no-toc'; ?>">
			<?php if ( count( $amk_toc ) > 1 ) : ?>
				<details class="contents" open><summary><?php echo esc_html( $amk_w['contents'] ); ?></summary><nav aria-label="Article contents"><?php foreach ( $amk_toc as $amk_item ) : ?><a href="#<?php echo esc_attr( $amk_item[0] ); ?>"><?php echo esc_html( $amk_item[1] ); ?></a><?php endforeach; ?></nav></details>
			<?php else : ?>
				<p><?php echo esc_html( $amk_w['more_in'] ); ?></p><a href="<?php echo esc_url( amk_writing_url( array( 'theme' => $amk_key ) ) ); ?>"><?php echo esc_html( $amk_theme['bn'] ); ?></a>
			<?php endif; ?>
		</aside>
		<article <?php post_class( 'article-body' . ( $amk_pod ? ' podcast-body' : '' ) ); ?>>
			<?php echo $amk_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php wp_link_pages( array( 'before' => '<nav class="page-links" aria-label="Pages of this article">', 'after' => '</nav>' ) ); ?>
			<?php
			if ( $amk_part ) :
				$amk_index = $amk_part - 1;
				$amk_prev  = $amk_series[ $amk_index - 1 ] ?? 0;
				$amk_next  = $amk_series[ $amk_index + 1 ] ?? 0;
				?>
				<nav class="series-nav" aria-label="Series: স্রষ্টার অস্তিত্বের যুক্তি" lang="bn">
					<?php if ( $amk_next ) : ?>
						<a class="series-next" rel="next" href="<?php echo esc_url( get_permalink( $amk_next ) ); ?>"><span>পরের পর্ব · <?php echo esc_html( amk_bn_digits( $amk_part + 1 ) . ' / ' . amk_bn_digits( count( $amk_series ) ) ); ?></span><strong><?php echo esc_html( get_the_title( $amk_next ) ); ?></strong></a>
					<?php else : ?>
						<p class="series-done">ধারাবাহিকের সর্বশেষ পর্ব এটি। <a href="<?php echo esc_url( amk_series_url() ); ?>">সব পর্ব দেখুন</a></p>
					<?php endif; ?>
					<?php if ( $amk_prev ) : ?>
						<a class="series-prev" rel="prev" href="<?php echo esc_url( get_permalink( $amk_prev ) ); ?>">← আগের পর্ব: <?php echo esc_html( get_the_title( $amk_prev ) ); ?></a>
					<?php endif; ?>
					<details class="series-list"><summary>সব পর্ব (<?php echo esc_html( amk_bn_digits( count( $amk_series ) ) ); ?>)</summary><ol>
						<?php foreach ( $amk_series as $amk_i => $amk_id ) : ?>
							<li<?php echo $amk_i === $amk_index ? ' aria-current="page"' : ''; ?>><?php if ( $amk_i === $amk_index ) : ?><?php echo esc_html( get_the_title( $amk_id ) ); ?><?php else : ?><a href="<?php echo esc_url( get_permalink( $amk_id ) ); ?>"><?php echo esc_html( get_the_title( $amk_id ) ); ?></a><?php endif; ?></li>
						<?php endforeach; ?>
					</ol></details>
				</nav>
			<?php endif; ?>
			<div class="article-end">
				<div class="article-share">
					<span><?php echo esc_html( $amk_w['share'] ); ?></span>
					<a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $amk_url ) ); ?>" target="_blank" rel="noopener">Facebook</a>
					<a href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( wp_strip_all_tags( get_the_title() ) . ' ' . $amk_url ) ); ?>" target="_blank" rel="noopener">WhatsApp</a>
					<button class="plain-button copy-link" type="button" data-copied="<?php echo esc_attr( $amk_w['copied'] ); ?>" data-copy-fail="<?php echo esc_attr( $amk_w['copyfail'] ); ?>"><?php echo esc_html( $amk_w['copy'] ); ?></button><span class="copy-status sr-only" role="status"></span>
				</div>
				<div class="article-end-links"><span><?php echo esc_html( $amk_pod ? 'End of episode notes' : $amk_w['end'] ); ?></span><a href="<?php echo esc_url( amk_writing_url() ); ?>"><?php echo esc_html( $amk_w['back'] ); ?> →</a></div>
			</div>
			<div class="author-note"><img src="<?php echo esc_url( amk_img( 'portrait-128.webp' ) ); ?>" alt="" width="56" height="56" loading="lazy"><div><a href="<?php echo esc_url( amk_page_url( 'biography' ) ); ?>">Dr. Md Abdullah Saeed Khan</a><p><?php echo esc_html( $amk_w['author'] ); ?></p></div></div>
		</article>
	</div>
	<?php
	// Related reading: same theme and language first, then the newest essays in the same language.
	// Parts of the series are left out on series posts: the series links above already list them.
	$amk_exclude = array_merge( array( get_the_ID() ), $amk_part ? $amk_series : array() );
	$amk_pool    = get_posts( array(
		'category__in'        => amk_theme_category_ids( $amk_key ),
		'post__not_in'        => $amk_exclude,
		'posts_per_page'      => 9,
		'ignore_sticky_posts' => true,
	) );
	$amk_pool    = array_merge( $amk_pool, get_posts( array(
		'category__not_in'    => amk_theme_category_ids( 'ai' ),
		'post__not_in'        => array_merge( $amk_exclude, wp_list_pluck( $amk_pool, 'ID' ) ),
		'posts_per_page'      => 9,
		'ignore_sticky_posts' => true,
	) ) );
	$amk_related = array();
	foreach ( array( true, false ) as $amk_match ) {
		foreach ( $amk_pool as $amk_p ) {
			if ( ( amk_post_is_bangla( $amk_p ) === $amk_bangla ) === $amk_match ) {
				$amk_related[ $amk_p->ID ] = $amk_p;
			}
		}
	}
	$amk_related = array_slice( array_values( $amk_related ), 0, 3 );
	if ( $amk_related ) :
		?>
		<section class="wrap section related-reading">
			<div class="section-heading"><h2<?php echo $amk_bangla ? ' lang="bn"' : ''; ?>><?php echo esc_html( $amk_w['continue'] ); ?></h2><a class="text-link" href="<?php echo esc_url( amk_writing_url( array( 'theme' => $amk_key ) ) ); ?>"><?php echo esc_html( $amk_w['topic'] ); ?> →</a></div>
			<div class="archive-grid"><?php foreach ( $amk_related as $amk_post ) { amk_card( $amk_post ); } ?></div>
		</section>
		<?php
	endif;
endwhile;
amk_footer();
