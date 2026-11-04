<?php
/**
 * Shared helpers: themes, language, dates, series, profile data, icons, header and footer.
 */

defined( 'ABSPATH' ) || exit;

/** Tag of the weekly "Arguments for God" series. */
const AMK_SERIES_TAG = 'arguments-for-god';

/**
 * Writing themes. Each groups WordPress categories (by id, or by slug for categories added later);
 * a post takes the first theme, in this order, that any of its categories belongs to.
 */
function amk_themes() {
	return array(
		'ai'         => array( 'bn' => 'AI in Health Research', 'en' => 'Daily research roundup', 'icon' => 'chip', 'cats' => array(), 'slugs' => array( 'ai-in-health-research' ) ),
		'health'     => array( 'bn' => 'চিকিৎসা ও গবেষণা', 'en' => 'Medicine & Research', 'icon' => 'health', 'cats' => array( 21, 26, 20 ) ),
		'mind'       => array( 'bn' => 'মন ও মস্তিষ্ক', 'en' => 'Mind & Brain', 'icon' => 'brain', 'cats' => array( 10, 19 ) ),
		'science'    => array( 'bn' => 'বিজ্ঞান ও বিবর্তন', 'en' => 'Science & Evolution', 'icon' => 'atom', 'cats' => array( 3, 11, 17, 27, 23 ) ),
		'faith'      => array( 'bn' => 'বিশ্বাস ও দর্শন', 'en' => 'Faith & Philosophy', 'icon' => 'faith', 'cats' => array( 8, 7, 25, 16, 22, 24, 28 ) ),
		'literature' => array( 'bn' => 'সাহিত্য ও রিভিউ', 'en' => 'Poems, Stories & Reviews', 'icon' => 'book', 'cats' => array( 1, 30, 14, 15, 9, 12, 29 ) ),
		'essays'     => array( 'bn' => 'ভাবনা ও প্রবন্ধ', 'en' => 'Essays & Reflections', 'icon' => 'pen', 'cats' => array( 13, 6, 18 ) ),
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

/** ?show=all on the writing page lists every post; the default lists the essays. */
function amk_writing_show() {
	return isset( $_GET['show'] ) && 'all' === $_GET['show'] ? 'all' : ''; // phpcs:ignore WordPress.Security.NonceVerification
}

function amk_is_bangla( $text ) {
	return (bool) preg_match( '/[\x{0980}-\x{09FF}]/u', (string) $text );
}

/** A post is Bangla when its title is, or when its text has more Bangla than Latin letters. */
function amk_post_is_bangla( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}
	if ( amk_is_bangla( $post->post_title ) ) {
		return true;
	}
	$text = wp_strip_all_tags( $post->post_content );
	return preg_match_all( '/[\x{0980}-\x{09FF}]/u', $text ) > preg_match_all( '/[A-Za-z]/', $text );
}

function amk_lower( $s ) {
	return function_exists( 'mb_strtolower' ) ? mb_strtolower( (string) $s, 'UTF-8' ) : strtolower( (string) $s );
}

function amk_bn_digits( $s ) {
	return strtr( (string) $s, array( '0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯' ) );
}

/** Bangla posts get a Bangla date ("১৭ ফেব্রুয়ারি, ২০২৪"), English posts "17 February 2024". */
function amk_date( $post = null, $format = 'j F Y' ) {
	$post = get_post( $post );
	if ( ! amk_post_is_bangla( $post ) ) {
		return get_the_date( $format, $post );
	}
	$months = array( 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর' );
	return amk_bn_digits( get_the_date( 'j', $post ) ) . ' ' . $months[ (int) get_the_date( 'n', $post ) - 1 ] . ', ' . amk_bn_digits( get_the_date( 'Y', $post ) );
}

function amk_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
}

function amk_writing_url( $args = array() ) {
	$base = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
	return $args ? add_query_arg( $args, $base ) : $base;
}

function amk_series_url() {
	$link = get_term_link( AMK_SERIES_TAG, 'post_tag' );
	return is_wp_error( $link ) ? amk_writing_url() : $link;
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

/** 'podcast', 'roundup' (the daily AI in Health Research posts) or 'article'. */
function amk_format( $post = null ) {
	if ( amk_podcast( $post ) ) {
		return 'podcast';
	}
	return 'ai' === amk_theme_of( $post ) ? 'roundup' : 'article';
}

/** Published parts of the series, oldest first (post ids). */
function amk_series_ids() {
	$ids = get_transient( 'amk_series_ids' );
	if ( false === $ids ) {
		$ids = get_posts( array(
			'tag'                 => AMK_SERIES_TAG,
			'posts_per_page'      => -1,
			'orderby'             => 'date',
			'order'               => 'ASC',
			'fields'              => 'ids',
			'ignore_sticky_posts' => true,
		) );
		set_transient( 'amk_series_ids', $ids, DAY_IN_SECONDS );
	}
	return array_map( 'intval', (array) $ids );
}

/** Part number (1-based) of a post in the series, or 0. */
function amk_series_part( $post = null ) {
	$index = array_search( (int) get_post( $post )->ID, amk_series_ids(), true );
	return false === $index ? 0 : $index + 1;
}

function amk_reading_minutes( $post = null ) {
	$words = preg_split( '/\s+/u', trim( wp_strip_all_tags( get_post( $post )->post_content ) ), -1, PREG_SPLIT_NO_EMPTY );
	return max( 1, (int) ceil( count( $words ) / 180 ) );
}

/** Reader-facing words on article pages follow the article's language. */
function amk_words( $bangla ) {
	if ( $bangla ) {
		return array(
			'lang'     => 'bn',
			'minutes'  => '%s মিনিটে পড়া',
			'role'     => 'চিকিৎসক · এপিডেমিওলজিস্ট · গবেষক',
			'contents' => 'এই লেখায়',
			'more_in'  => 'এই বিষয়ে আরও',
			'end'      => 'লেখা শেষ',
			'back'     => 'সব লেখায় ফিরুন',
			'share'    => 'শেয়ার করুন',
			'copy'     => 'লিংক কপি করুন',
			'copied'   => 'লিংক কপি হয়েছে',
			'copyfail' => 'ঠিকানা বার থেকে কপি করুন',
			'continue' => 'আরও পড়ুন',
			'topic'    => 'এই বিষয়ের সব লেখা',
			'read'     => 'পড়ুন',
			'author'   => 'চিকিৎসক, এপিডেমিওলজিস্ট ও গবেষক। অক্সফোর্ড বিশ্ববিদ্যালয়ে ক্লিনিক্যাল মেডিসিনে ডিফিল ফেলো।',
		);
	}
	return array(
		'lang'     => 'en',
		'minutes'  => '%s min read',
		'role'     => 'Physician · Epidemiologist · Researcher',
		'contents' => 'In this article',
		'more_in'  => 'More in',
		'end'      => 'End of article',
		'back'     => 'Back to all writing',
		'share'    => 'Share',
		'copy'     => 'Copy link',
		'copied'   => 'Link copied',
		'copyfail' => 'Copy from the address bar',
		'continue' => 'Continue reading',
		'topic'    => 'All writing on this topic',
		'read'     => 'Read article',
		'author'   => 'Physician, epidemiologist and researcher. DPhil fellow in Clinical Medicine at the University of Oxford.',
	);
}

/** Decoded amk_profile / amk_research options (arrays), or an empty array when not set. */
function amk_profile() {
	static $data = null;
	if ( null === $data ) {
		$data = json_decode( (string) get_option( 'amk_profile' ), true );
		$data = is_array( $data ) ? $data : array();
	}
	return $data;
}
function amk_research() {
	static $data = null;
	if ( null === $data ) {
		$data = json_decode( (string) get_option( 'amk_research' ), true );
		$data = is_array( $data ) ? $data : array();
	}
	return $data;
}

function amk_email() {
	$email = amk_profile()['email'] ?? '';
	return is_email( $email ) ? $email : 'abdullahdmc@gmail.com';
}

/** Line icons, drawn on a 24px grid in one stroke weight. */
function amk_icon( $name ) {
	$paths = array(
		'search'   => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
		'book'     => '<path d="M12 5C8 2 3 3 2 4v15c4-2 7-1 10 1 3-2 6-3 10-1V4c-4-2-7-1-10 1Zm0 0v15"/>',
		'brain'    => '<path d="M12 4C5-1 1 7 5 10c-5 4-1 11 5 9l2 2 2-2c6 2 10-5 5-9 4-3 0-11-7-6Zm0 0v17M6 8l3 2M5 14l4-1m9-5-3 2m4 4-4-1"/>',
		'atom'     => '<ellipse cx="12" cy="12" rx="10" ry="4"/><ellipse cx="12" cy="12" rx="10" ry="4" transform="rotate(60 12 12)"/><ellipse cx="12" cy="12" rx="10" ry="4" transform="rotate(120 12 12)"/>',
		'health'   => '<path d="M5 3v7a5 5 0 0 0 10 0V3M3 3h4m6 0h4M10 15v2a4 4 0 0 0 8 0v-3"/><circle cx="18" cy="11" r="3"/>',
		'chip'     => '<rect x="6" y="6" width="12" height="12" rx="2"/><path d="M9 2v4m6-4v4M9 18v4m6-4v4M2 9h4m-4 6h4m12-6h4m-4 6h4"/>',
		'pen'      => '<path d="m4 16-1 5 5-1L21 7l-5-5L4 16Zm10-12 5 5M4 16l4 4"/>',
		'faith'    => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3v18m-6-15 12 12M6 18 18 6"/>',
		'play'     => '<path d="m9 5 11 7-11 7V5Z"/>',
		'scholar'  => '<path d="M12 3 2 9l10 6 10-6-10-6Z"/><path d="M6 11.5V16c0 1.7 2.7 3 6 3s6-1.3 6-3v-4.5"/>',
		'rg'       => '<rect x="3" y="3" width="18" height="18" rx="3"/><text x="12" y="16" text-anchor="middle" font-size="9" font-family="Georgia,serif" fill="currentColor" stroke="none">RG</text>',
		'orcid'    => '<circle cx="12" cy="12" r="9"/><path d="M9 10v6M9 7.5v.1M12 10h2a3 3 0 0 1 0 6h-2v-6Z"/>',
		'scopus'   => '<circle cx="12" cy="12" r="9"/><path d="M15 9.5c-.6-.9-1.7-1.5-3-1.5-1.7 0-3 1-3 2.2 0 2.8 6 1.4 6 4.3 0 1.3-1.3 2.5-3 2.5-1.4 0-2.6-.6-3.2-1.6"/>',
		'wos'      => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3Z"/>',
		'github'   => '<path d="M9 19c-4 1.3-4-2-6-2.5M15 21v-3.5c0-1 .1-1.4-.5-2 2.8-.3 5.5-1.4 5.5-6a4.6 4.6 0 0 0-1.3-3.2 4.2 4.2 0 0 0-.1-3.2s-1-.3-3.4 1.3a11.6 11.6 0 0 0-6 0C6.8 2.8 5.8 3.1 5.8 3.1a4.2 4.2 0 0 0-.1 3.2A4.6 4.6 0 0 0 4.4 9.5c0 4.6 2.7 5.7 5.5 6-.6.6-.6 1.2-.5 2V21"/>',
		'linkedin' => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10v7M8 7v.1M12 17v-4a2 2 0 0 1 4 0v4M12 10v7"/>',
		'facebook' => '<path d="M15 3h-2.5A3.5 3.5 0 0 0 9 6.5V10H6.5v3.5H9V21h3.5v-7.5H15l.5-3.5h-3V7a1 1 0 0 1 1-1H15V3Z"/>',
		'x'        => '<path d="M4 4l16 16M20 4 4 20"/>',
		'youtube'  => '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="m10 9 5 3-5 3V9Z" fill="currentColor"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
		'spotify'  => '<circle cx="12" cy="12" r="9.5"/><path d="M7.5 9.6c3-1 6.4-.8 9.2.7M8 12.6c2.5-.7 5.2-.5 7.4.7M8.6 15.4c1.9-.5 3.9-.3 5.6.6"/>',
	);
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $paths[ $name ] ?? $paths['book'] ) . '</svg>';
}

/**
 * Page-header picture for listing pages (assets/img/<name>-480|960|1200.webp):
 * books on Writing, reason on Faith & Philosophy, door on the series and "not found" pages.
 */
function amk_intro_art( $name, $alt ) {
	$src = function ( $w ) use ( $name ) {
		return amk_img( $name . '-' . $w . '.webp' );
	};
	return '<figure class="intro-art"><img src="' . esc_url( $src( 960 ) ) . '" srcset="' . esc_attr( $src( 480 ) . ' 480w, ' . $src( 960 ) . ' 960w, ' . $src( 1200 ) . ' 1200w' ) . '" sizes="(max-width:800px) calc(100vw - 40px), 460px" width="1200" height="800" alt="' . esc_attr( $alt ) . '" fetchpriority="high"></figure>';
}

function amk_arrow() {
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 12h15m-6-6 6 6-6 6"/></svg>';
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
<body <?php echo is_front_page() ? '' : 'id="top" '; ?><?php body_class(); ?>>
<?php wp_body_open(); ?>
	<?php
}

function amk_header( $active = '' ) {
	$links = array(
		'writing'      => array( amk_writing_url(), 'Writing' ),
		'research'     => array( amk_page_url( 'research' ), 'Research' ),
		'biography'    => array( amk_page_url( 'biography' ), 'Biography' ),
		'consultation' => array( amk_page_url( 'consultation' ), 'Consultation' ),
	);
	?>
<a class="skip-link" href="#main">Skip to content</a>
<header class="header"><div class="wrap header-inner">
	<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">Md Abdullah Saeed Khan<span>MBBS, MPH, MPhil, DPhil (Ongoing)</span></a>
	<nav id="main-nav" aria-label="Main navigation">
		<?php foreach ( $links as $key => $link ) : ?>
			<a href="<?php echo esc_url( $link[0] ); ?>"<?php echo $key === $active ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $link[1] ); ?></a>
		<?php endforeach; ?>
	</nav>
	<div class="header-tools">
		<button class="icon-button theme-toggle" id="theme-toggle" type="button" aria-label="Use dark theme"><svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5Z"/></svg><svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2.5v2M12 19.5v2M4.6 4.6l1.4 1.4M18 18l1.4 1.4M2.5 12h2M19.5 12h2M4.6 19.4 6 18M18 6l1.4-1.4"/></svg></button>
		<button class="icon-button" id="search-toggle" type="button" aria-label="Search articles" aria-expanded="false" aria-controls="search-panel"><?php echo amk_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
		<button class="menu-toggle" id="menu-toggle" type="button" aria-expanded="false" aria-controls="main-nav"><span></span><span></span><span class="sr-only">Menu</span></button>
	</div>
</div>
<section id="search-panel" class="search-panel" hidden aria-label="Search writing"><div class="wrap">
	<label for="site-search">Find an idea</label>
	<div class="search-row"><input id="site-search" type="search" placeholder="Search in Bangla or English…" autocomplete="off"><button class="close-search" type="button">Close <span aria-hidden="true">×</span></button></div>
	<p class="search-hint" id="search-status" role="status">Search the writing and AI research roundups.</p>
	<ul id="search-results"></ul>
</div></section>
</header>
<main id="main">
	<?php
}

function amk_footer() {
	$links = array(
		'Writing'      => amk_writing_url(),
		'Research'     => amk_page_url( 'research' ),
		'Biography'    => amk_page_url( 'biography' ),
		'Consultation' => amk_page_url( 'consultation' ),
	);
	?>
</main>
<footer class="footer">
	<?php amk_newsletter(); ?>
	<div class="wrap footer-bottom">
		<div><a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">Md Abdullah Saeed Khan</a><p>© <?php echo esc_html( wp_date( 'Y' ) ); ?> Md Abdullah Saeed Khan</p><a href="mailto:<?php echo esc_attr( amk_email() ); ?>"><?php echo esc_html( amk_email() ); ?></a></div>
		<nav aria-label="Footer navigation"><?php foreach ( $links as $label => $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a><?php endforeach; ?></nav>
		<a class="back-top" href="#top" aria-label="Back to top">↑</a>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
	<?php
}

/**
 * Footer sign-up box: the MailPoet form chosen in the amk_newsletter_form option. The consultation
 * page keeps one call to action, so it has no sign-up box; without a configured form there is none.
 */
function amk_newsletter() {
	$form = (int) get_option( 'amk_newsletter_form' );
	if ( ! $form || ! shortcode_exists( 'mailpoet_form' ) || 'templates/template-consultation.php' === get_page_template_slug() ) {
		return;
	}
	?>
	<div class="wrap newsletter">
		<div><h2 lang="bn">নতুন লেখা পৌঁছে যাক<br>আপনার ইনবক্সে</h2><p>Get new essays and research notes by email. No spam, unsubscribe anytime.</p></div>
		<div class="subscribe amk-mailpoet"><?php echo do_shortcode( '[mailpoet_form id="' . $form . '"]' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
	</div>
	<?php
}
