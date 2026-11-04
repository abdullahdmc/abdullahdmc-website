<?php
/**
 * Shared template helpers: themes, dates, cards, header and footer.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Writing themes. Each groups WordPress categories (by id, or by slug for categories added later);
 * a post takes the first theme, in this order, that any of its categories belongs to.
 */
function amk_themes() {
	return array(
		'ai'         => array( 'bn' => 'AI in Health Research', 'en' => 'Daily research roundup', 'c1' => '#0b3d5c', 'c2' => '#1f8ab0', 'cats' => array(), 'slugs' => array( 'ai-in-health-research' ) ),
		'health'     => array( 'bn' => 'চিকিৎসা ও গবেষণা', 'en' => 'Medicine & Research', 'c1' => '#9b3434', 'c2' => '#d0644a', 'cats' => array( 21, 26, 20 ) ),
		'mind'       => array( 'bn' => 'মন ও মস্তিষ্ক', 'en' => 'Mind & Brain', 'c1' => '#3f3685', 'c2' => '#6a5fc1', 'cats' => array( 10, 19 ) ),
		'science'    => array( 'bn' => 'বিজ্ঞান ও বিবর্তন', 'en' => 'Science & Evolution', 'c1' => '#0f5c5a', 'c2' => '#178f86', 'cats' => array( 3, 11, 17, 27, 23 ) ),
		'faith'      => array( 'bn' => 'বিশ্বাস ও দর্শন', 'en' => 'Faith & Philosophy', 'c1' => '#1d3f66', 'c2' => '#3a6ea5', 'cats' => array( 8, 7, 25, 16, 22, 24, 28 ) ),
		'literature' => array( 'bn' => 'সাহিত্য ও রিভিউ', 'en' => 'Poems, Stories & Reviews', 'c1' => '#7a4a17', 'c2' => '#c08a3e', 'cats' => array( 1, 30, 14, 15, 9, 12, 29 ) ),
		'essays'     => array( 'bn' => 'ভাবনা ও প্রবন্ধ', 'en' => 'Essays & Reflections', 'c1' => '#34433a', 'c2' => '#5f7a67', 'cats' => array( 13, 6, 18 ) ),
	);
}

function amk_theme_category_ids( $key ) {
	$themes = amk_themes();
	if ( empty( $themes[ $key ] ) ) {
		return array();
	}
	$ids = $themes[ $key ]['cats'];
	foreach ( $themes[ $key ]['slugs'] ?? array() as $slug ) {
		$term = get_category_by_slug( $slug );
		if ( $term ) {
			$ids[] = (int) $term->term_id;
		}
	}
	return $ids;
}

function amk_theme_of( $post = null ) {
	$cats = wp_get_post_categories( get_post( $post )->ID );
	foreach ( array_keys( amk_themes() ) as $key ) {
		if ( array_intersect( $cats, amk_theme_category_ids( $key ) ) ) {
			return $key;
		}
	}
	return 'essays';
}

function amk_is_bangla( $text ) {
	return (bool) preg_match( '/[\x{0980}-\x{09FF}]/u', $text );
}

function amk_bn_digits( $s ) {
	return strtr( (string) $s, array( '0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯' ) );
}

// Bangla posts get a Bangla date ("১৭ ফেব্রুয়ারি ২০২৪"), English posts "17 Feb 2024".
function amk_date( $post = null ) {
	$post = get_post( $post );
	if ( ! amk_is_bangla( $post->post_title ) ) {
		return get_the_date( 'j M Y', $post );
	}
	$months = array( 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর' );
	return amk_bn_digits( get_the_date( 'j', $post ) ) . ' ' . $months[ (int) get_the_date( 'n', $post ) - 1 ] . ' ' . amk_bn_digits( get_the_date( 'Y', $post ) );
}

function amk_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

function amk_writing_url( $args = array() ) {
	$base = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
	return $args ? add_query_arg( $args, $base ) : $base;
}

/** Featured image, or a plain gradient with the title when a post has none. */
function amk_cover( $post = null, $size = 'amk-card', $class = '' ) {
	$post  = get_post( $post );
	$theme = amk_themes()[ amk_theme_of( $post ) ];
	if ( has_post_thumbnail( $post ) ) {
		// Large covers sit at the top of the page, so they load straight away; card covers load lazily.
		$loading = 'lg' === $class ? array( 'loading' => 'eager', 'fetchpriority' => 'high' ) : array( 'loading' => 'lazy' );
		return '<div class="cover-img ' . esc_attr( $class ) . '">' . get_the_post_thumbnail( $post, $size, array( 'alt' => esc_attr( get_the_title( $post ) ) ) + $loading ) . '</div>';
	}
	return sprintf(
		'<div class="cover %s" style="--c1:%s;--c2:%s"><span class="c-chip">%s</span><span class="c-sig">abdullahdmc.com</span><div class="c-title">%s</div></div>',
		esc_attr( $class ), esc_attr( $theme['c1'] ), esc_attr( $theme['c2'] ), esc_html( $theme['bn'] ), esc_html( get_the_title( $post ) )
	);
}

/**
 * Podcast episodes are posts tagged "podcast" whose content starts with the audio player
 * (written by tools/publish_podcasts.py). Returns [audio url, duration] or null.
 */
function amk_podcast( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || ! has_tag( 'podcast', $post ) ) {
		return null;
	}
	$src = preg_match( '/<audio[^>]+src="([^"]+)"/', $post->post_content, $m ) ? $m[1] : '';
	$len = preg_match( '/data-duration="([^"]*)"/', $post->post_content, $d ) ? $d[1] : '';
	return array( $src, $len );
}

function amk_headphones_icon() {
	return '<svg class="hp" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 15v-3a8 8 0 0 1 16 0v3"/><rect x="3" y="14" width="4" height="7" rx="1.5"/><rect x="17" y="14" width="4" height="7" rx="1.5"/></svg>';
}

/** "Podcast · 14:02" label for cards and lists. */
function amk_podcast_chip( $post = null ) {
	$pod = amk_podcast( $post );
	return $pod ? '<span class="pod-chip">' . amk_headphones_icon() . 'Podcast' . ( $pod[1] ? ' · ' . esc_html( $pod[1] ) : '' ) . '</span>' : '';
}

function amk_card( $post = null ) {
	$post  = get_post( $post );
	$theme = amk_themes()[ amk_theme_of( $post ) ];
	?>
	<a class="card" href="<?php echo esc_url( get_permalink( $post ) ); ?>" style="--c1:<?php echo esc_attr( $theme['c1'] ); ?>">
		<?php echo amk_cover( $post ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<div class="card-body">
			<div class="card-meta"><?php echo amk_podcast_chip( $post ); // phpcs:ignore ?><span class="dot"></span><?php echo esc_html( $theme['bn'] . ' · ' . amk_date( $post ) ); ?></div>
			<h3><?php echo esc_html( get_the_title( $post ) ); ?></h3>
			<p><?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $post ) ) ); ?></p>
		</div>
	</a>
	<?php
}

/** Profile and research JSON for the page scripts (window.PROFILE, window.RESEARCH). */
function amk_data_script() {
	$profile  = get_option( 'amk_profile' );
	$research = get_option( 'amk_research' );
	return 'window.PROFILE=' . ( $profile ? $profile : 'null' ) . ';window.RESEARCH=' . ( $research ? $research : 'null' ) . ';'
		. 'window.AMK=' . wp_json_encode( array(
			'writing'  => amk_writing_url(),
			'research' => amk_page_url( 'research' ),
		) ) . ';';
}

function amk_head() {
	?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
	<?php
}

function amk_header( $active = '' ) {
	$links = array(
		'home'         => array( home_url( '/' ), 'Home' ),
		'writing'      => array( amk_writing_url(), 'লেখা · Writing' ),
		'research'     => array( amk_page_url( 'research' ), 'Research' ),
		'biography'    => array( amk_page_url( 'biography' ), 'Biography' ),
		'consultation' => array( amk_page_url( 'consultation' ), 'Consultation' ),
	);
	?>
<a class="screen-reader-text skip-link" href="#main">Skip to content</a>
<header class="site-header"><div class="wrap">
	<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span class="brand-mark">AK</span>
		<span class="brand-name">Md Abdullah Saeed Khan<small>MBBS, MPH, MPhil, DPhil (Ongoing)</small><small>Assistant Professor (Epidemiology)</small><small>Health Research &amp; Data Analytics Consultant</small></span></a>
	<button class="menu-btn" aria-label="Menu" onclick="document.querySelector('.nav').classList.toggle('open')">☰</button>
	<nav class="nav" aria-label="Main">
		<?php foreach ( $links as $key => $link ) : ?>
			<a href="<?php echo esc_url( $link[0] ); ?>" class="<?php echo 'consultation' === $key ? 'nav-consult ' : ''; ?><?php echo $key === $active ? 'active' : ''; ?>"><?php echo esc_html( $link[1] ); ?></a>
		<?php endforeach; ?>
	</nav>
</div></header>
<main id="main">
	<?php
}

function amk_footer() {
	?>
</main>
<footer class="site-footer"><div class="wrap">
	<div>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Md Abdullah Saeed Khan<br><a href="mailto:abdullahdmc@gmail.com">abdullahdmc@gmail.com</a></div>
	<div class="footer-icons" id="footer-icons"></div>
	<div><a href="<?php echo esc_url( amk_writing_url() ); ?>">Writing</a><a href="<?php echo esc_url( amk_page_url( 'research' ) ); ?>">Research</a><a href="<?php echo esc_url( amk_page_url( 'biography' ) ); ?>">Biography</a><a href="<?php echo esc_url( amk_page_url( 'consultation' ) ); ?>">Consultation</a></div>
</div></footer>
<?php wp_footer(); ?>
</body>
</html>
	<?php
}

/** Grid of post cards for the current main query, with numbered pagination. */
function amk_post_grid() {
	if ( ! have_posts() ) {
		echo '<div class="empty">কোনো লেখা পাওয়া যায়নি। · Nothing found.</div>';
		return;
	}
	echo '<div class="grid">';
	while ( have_posts() ) {
		the_post();
		amk_card();
	}
	echo '</div>';
	echo '<nav class="pager">' . paginate_links( array( 'prev_text' => '← আগের', 'next_text' => 'পরের →', 'mid_size' => 1 ) ) . '</nav>'; // phpcs:ignore
}

/** Theme filter chips for the writing page. */
function amk_theme_chips( $current ) {
	echo '<div class="chips">';
	printf( '<a class="chip" aria-pressed="%s" href="%s">সব</a>', $current ? 'false' : 'true', esc_url( amk_writing_url() ) );
	foreach ( amk_themes() as $key => $t ) {
		$count = 0;
		foreach ( amk_theme_category_ids( $key ) as $id ) {
			$term   = get_term( $id, 'category' );
			$count += ( $term && ! is_wp_error( $term ) ) ? (int) $term->count : 0;
		}
		if ( ! $count ) {
			continue;
		}
		printf(
			'<a class="chip" aria-pressed="%s" href="%s"><span class="dot" style="--c1:%s"></span>%s</a>',
			$key === $current ? 'true' : 'false', esc_url( amk_writing_url( array( 'theme' => $key ) ) ), esc_attr( $t['c1'] ), esc_html( $t['bn'] )
		);
	}
	echo '</div>';
}
