<?php
/**
 * Template components: cards, images, the writing grid, breadcrumbs, timelines and article text.
 */

defined( 'ABSPATH' ) || exit;

function amk_crumb( $label, $middle = array() ) {
	echo '<nav class="crumb" aria-label="Breadcrumb"><a href="' . esc_url( home_url( '/' ) ) . '">Home</a>';
	foreach ( $middle as $text => $url ) {
		echo '<span aria-hidden="true">/</span><a href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>';
	}
	echo '<span aria-hidden="true">/</span><span>' . esc_html( $label ) . '</span></nav>';
}

/** The post's cover image, or a type-only cover with the theme name when it has none. */
function amk_post_image( $post, $size = 'amk-card', $sizes = '(max-width:800px) calc(100vw - 40px), 380px', $loading = 'lazy' ) {
	$post = get_post( $post );
	if ( has_post_thumbnail( $post ) ) {
		return get_the_post_thumbnail( $post, $size, array(
			'alt'     => '',
			'sizes'   => $sizes,
			'loading' => $loading,
		) );
	}
	$name = amk_themes()[ amk_theme_of( $post ) ]['bn'];
	return '<span class="type-cover"><span' . ( amk_is_bangla( $name ) ? ' lang="bn"' : '' ) . '>' . esc_html( $name ) . '</span></span>';
}

function amk_part_chip( $post = null ) {
	$part = amk_series_part( $post );
	if ( ! $part ) {
		return '';
	}
	return '<span class="part-chip" lang="bn">পর্ব ' . esc_html( amk_bn_digits( $part ) . ' / ' . amk_bn_digits( count( amk_series_ids() ) ) ) . '</span>';
}

/** One entry of the writing grid. */
function amk_card( $post = null ) {
	$post   = get_post( $post );
	$key    = amk_theme_of( $post );
	$theme  = amk_themes()[ $key ];
	$bangla = amk_post_is_bangla( $post );
	$format = amk_format( $post );
	$url    = get_permalink( $post );
	$title  = get_the_title( $post );
	$lang   = $bangla ? ' lang="bn"' : '';
	if ( 'podcast' === $format ) {
		$pod   = amk_podcast( $post );
		$image = '<span class="podcast-art"><span>Podcast' . ( $pod[1] ? ' · ' . esc_html( $pod[1] ) : '' ) . '</span><strong>AI in Health<br>Research</strong><span class="podcast-wave" aria-hidden="true">▂ ▅ ▃ ▇ ▄ ▆ ▂ ▅ ▇ ▃ ▆ ▂ ▄ ▆ ▃</span><span>Listen to the episode →</span></span>';
		$cta   = 'Listen to episode' . ( $pod[1] ? ' · ' . esc_html( $pod[1] ) : '' );
	} else {
		$image = amk_post_image( $post );
		$cta   = 'roundup' === $format ? 'Read roundup' : esc_html( amk_words( $bangla )['read'] );
	}
	?>
	<article class="archive-card">
		<a class="archive-image" href="<?php echo esc_url( $url ); ?>" tabindex="-1" aria-hidden="true"><?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<div class="meta"><a href="<?php echo esc_url( amk_writing_url( array( 'theme' => $key ) ) ); ?>"<?php echo amk_is_bangla( $theme['bn'] ) ? ' lang="bn"' : ''; ?>><?php echo esc_html( $theme['bn'] ); ?></a><time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $post ) ); ?>"<?php echo $lang; // phpcs:ignore ?>><?php echo esc_html( amk_date( $post ) ); ?></time><?php echo amk_part_chip( $post ); // phpcs:ignore ?></div>
		<h2<?php echo $lang; // phpcs:ignore ?>><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $title ); ?></a></h2>
		<p<?php echo $lang; // phpcs:ignore ?>><?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $post ) ) ); ?></p>
		<a class="text-link" href="<?php echo esc_url( $url ); ?>"<?php echo 'article' === $format ? $lang : ''; // phpcs:ignore ?>><?php echo $cta; // phpcs:ignore ?> <span aria-hidden="true">→</span><span class="sr-only">: <?php echo esc_html( $title ); ?></span></a>
	</article>
	<?php
}

/** Cards for the current main query, with numbered pages. */
function amk_grid( $skip = 0 ) {
	if ( ! have_posts() ) {
		echo '<p class="empty-state">No writing matches. Try another word or topic, or <a href="' . esc_url( amk_writing_url() ) . '">browse all writing</a>.</p>';
		return;
	}
	echo '<div class="archive-grid">';
	$n = 0;
	while ( have_posts() ) {
		the_post();
		if ( $n++ < $skip ) {
			continue;
		}
		amk_card();
	}
	echo '</div>';
	$pages = paginate_links( array(
		'type'      => 'array',
		'mid_size'  => 1,
		'prev_text' => '<span aria-hidden="true">←</span><span class="sr-only">Previous page</span>',
		'next_text' => '<span aria-hidden="true">→</span><span class="sr-only">Next page</span>',
	) );
	if ( $pages ) {
		echo '<nav class="pagination wp-pagination" aria-label="Writing pages">' . implode( '', $pages ) . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	wp_reset_postdata();
}

/** Rows of a CV timeline: [title, organisation, year, note]. */
function amk_timeline( $rows ) {
	if ( ! $rows ) {
		return;
	}
	echo '<div class="timeline">';
	foreach ( $rows as $row ) {
		$row = array_values( (array) $row ) + array( '', '', '', '' );
		echo '<article><span>' . esc_html( $row[2] ) . '</span><div><h3>' . esc_html( $row[0] ) . '</h3><p>' . esc_html( $row[1] ) . '</p>' . ( $row[3] ? '<p>' . esc_html( $row[3] ) . '</p>' : '' ) . '</div></article>';
	}
	echo '</div>';
}

/** Profile links ([name, url, icon]) as small labelled links; with icons on the home page. */
function amk_profile_links( $links, $icons = false ) {
	if ( ! $links ) {
		return;
	}
	echo '<div class="profile-links">';
	foreach ( $links as $link ) {
		list( $name, $url ) = $link;
		echo '<a href="' . esc_url( $url ) . '">' . ( $icons ? amk_icon( $link[2] ?? '' ) . '<span>' . esc_html( $name ) . '</span>' . amk_arrow() : esc_html( $name ) . ' <span aria-hidden="true">↗</span>' ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</div>';
}

/**
 * Article text with anchored headings. The content's top heading level becomes h2 (the page title
 * is the h1), and each heading gets an id for the contents list. Returns [html, [[id, text], ...]].
 */
function amk_article_content() {
	$html  = apply_filters( 'the_content', get_the_content() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$html  = str_replace( ']]>', ']]&gt;', $html );
	$toc   = array();
	$used  = array();
	preg_match_all( '/<h([1-6])[\s>]/i', $html, $levels );
	$shift = $levels[1] ? 2 - min( array_map( 'intval', $levels[1] ) ) : 0;
	$html  = preg_replace_callback( '/<h([1-6])(\s[^>]*)?>(.*?)<\/h\1>/is', function ( $m ) use ( &$toc, &$used, $shift ) {
		$level = min( 6, max( 2, (int) $m[1] + $shift ) );
		$attrs = $m[2] ?? '';
		$text  = trim( wp_strip_all_tags( $m[3] ) );
		if ( preg_match( '/\sid="([^"]+)"/', $attrs, $id ) ) {
			$id = $id[1];
		} else {
			$id    = 'section-' . ( count( $used ) + 1 );
			$attrs = ' id="' . $id . '"' . $attrs;
		}
		$used[] = $id;
		if ( '' !== $text ) {
			$toc[] = array( $id, $text );
		}
		return '<h' . $level . $attrs . '>' . $m[3] . '</h' . $level . '>';
	}, $html );
	// Old posts mark breaks with a paragraph of dots; show them as a section break.
	$html = preg_replace( '/<p[^>]*>\s*[.…·]{2,}\s*<\/p>/u', '<hr>', $html );
	return array( $html, $toc );
}
