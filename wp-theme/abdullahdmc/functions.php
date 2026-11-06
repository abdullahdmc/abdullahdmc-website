<?php
/**
 * Abdullah Saeed Khan: child theme of Astra.
 *
 * Every front-end template is provided here, so Astra's own header, footer and styles are not used.
 * Profile and research data are stored as JSON in two options (amk_profile, amk_research) that an
 * administrator can update through the REST API (/wp-json/wp/v2/settings); the theme only reads them.
 */

defined( 'ABSPATH' ) || exit;

define( 'AMK_VERSION', '2.0.3' );

require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/components.php';
require_once __DIR__ . '/inc/search.php';
require_once __DIR__ . '/inc/seo.php';

add_action( 'after_setup_theme', function () {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_image_size( 'amk-card', 800, 450, true );
	add_image_size( 'amk-cover', 1600, 900, true );
} );

// Profile and research data, editable by administrators over REST, readable by the theme.
add_action( 'init', function () {
	// Name of the line drawing on the post's cover (set by tools/make_covers.py), shown on series cards.
	register_post_meta( 'post', 'amk_motif', array(
		'type'              => 'string',
		'single'            => true,
		'default'           => '',
		'show_in_rest'      => true,
		'sanitize_callback' => 'sanitize_key',
		'auth_callback'     => function () {
			return current_user_can( 'edit_posts' );
		},
	) );
	// Extra CSS for small design tweaks without re-uploading the theme.
	register_setting( 'general', 'amk_css', array(
		'type'              => 'string',
		'default'           => '',
		'show_in_rest'      => true,
		'sanitize_callback' => function ( $value ) {
			return wp_strip_all_tags( (string) $value );
		},
	) );
	// Default social preview image (attachment id) for pages without a cover.
	register_setting( 'general', 'amk_og_image', array(
		'type'         => 'integer',
		'default'      => 0,
		'show_in_rest' => true,
	) );
	// MailPoet form shown in the footer sign-up box (0 = no box).
	register_setting( 'general', 'amk_newsletter_form', array(
		'type'         => 'integer',
		'default'      => 0,
		'show_in_rest' => true,
	) );
	foreach ( array( 'amk_profile', 'amk_research' ) as $name ) {
		register_setting( 'general', $name, array(
			'type'              => 'string',
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => function ( $value ) {
				return null === json_decode( (string) $value ) ? '' : (string) $value;
			},
		) );
	}
} );

/**
 * URL of a theme asset with the version in its file name (base-2.0.0.css, made by build-assets.mjs).
 * Namecheap's Supersonic CDN ignores "?ver=", so a new file name is the only reliable way to make
 * every visitor get the new file after a theme update.
 */
function amk_asset( $name ) {
	$versioned = preg_replace( '/\.(css|js)$/', '-' . AMK_VERSION . '.$1', $name );
	$file      = file_exists( get_stylesheet_directory() . '/assets/' . $versioned ) ? $versioned : $name;
	return get_stylesheet_directory_uri() . '/assets/' . $file;
}

/** URL of an image in assets/img/. */
function amk_img( $name ) {
	return get_stylesheet_directory_uri() . '/assets/img/' . $name;
}

add_action( 'wp_enqueue_scripts', function () {
	// Astra's styles and scripts are not needed: our templates render the whole page.
	foreach ( wp_styles()->queue as $handle ) {
		if ( 0 === strpos( $handle, 'astra' ) ) {
			wp_dequeue_style( $handle );
		}
	}
	foreach ( wp_scripts()->queue as $handle ) {
		if ( 0 === strpos( $handle, 'astra' ) ) {
			wp_dequeue_script( $handle );
		}
	}
	// Self-hosted fonts, split by script so a page loads only the Bengali or Latin files it uses.
	wp_enqueue_style( 'amk-fonts', amk_asset( 'fonts.css' ), array(), null );
	wp_enqueue_style( 'amk-base', amk_asset( 'base.css' ), array( 'amk-fonts' ), null );
	wp_enqueue_style( 'amk-pages', amk_asset( 'pages.css' ), array( 'amk-base' ), null );
	wp_enqueue_style( 'amk-wp', amk_asset( 'wp.css' ), array( 'amk-pages' ), null );
	if ( get_option( 'amk_css' ) ) {
		wp_add_inline_style( 'amk-wp', get_option( 'amk_css' ) );
	}
	wp_enqueue_script( 'amk-site', amk_asset( 'site.js' ), array(), null, true );
	wp_add_inline_script( 'amk-site', 'window.AMK=' . wp_json_encode( array(
		'searchIndex' => rest_url( 'amk/v1/search-index' ),
		'search'      => home_url( '/?s=' ),
	) ) . ';', 'before' );
	wp_enqueue_script( 'amk-pages', amk_asset( 'pages.js' ), array( 'amk-site' ), null, true );
}, 100 );

// Theme and colour scheme are set before the first paint, so a saved dark choice never flashes light.
add_action( 'wp_head', function () {
	?>
<meta name="color-scheme" content="light dark">
<meta name="theme-color" content="#0e1d19" media="(prefers-color-scheme: dark)">
<meta name="theme-color" content="#102d27">
<script>document.documentElement.classList.add('js');try{var t=localStorage.getItem('theme');if(t==='light'||t==='dark')document.documentElement.dataset.theme=t}catch(e){}</script>
	<?php
	if ( is_front_page() ) {
		echo '<link rel="preload" href="' . esc_url( amk_img( 'hero-balanced.webp' ) ) . '" as="image" fetchpriority="high">' . "\n";
	}
	if ( ! has_site_icon() ) {
		echo '<link rel="icon" href="' . esc_url( amk_img( 'favicon.svg' ) ) . '" type="image/svg+xml">' . "\n";
	}
}, 1 );

// Inner pages share one layout; pages with no Bangla text of their own skip the Bangla serif font.
add_filter( 'body_class', function ( $classes ) {
	if ( ! is_front_page() ) {
		$classes[] = 'inner-page';
	}
	if ( is_404() || in_array( get_page_template_slug(), array( 'templates/template-research.php', 'templates/template-biography.php', 'templates/template-consultation.php' ), true ) ) {
		$classes[] = 'latin-page';
	}
	return $classes;
} );

// Astra sets the list length again after pre_get_posts (on parse_tax_query), so tell Astra too.
add_filter( 'astra_blog_post_per_page', function () {
	return 12;
} );

// Astra's scroll-to-top button has no styling without Astra's CSS.
add_filter( 'astra_get_option_scroll-to-top-enable', '__return_false' );

add_filter( 'excerpt_length', function () {
	return 32;
} );
add_filter( 'excerpt_more', function () {
	return '…';
} );

/**
 * Writing page filters.
 *   ?theme=science         one theme's categories
 *   ?theme=ai&kind=podcasts|roundups   AI in Health Research, by kind
 *   ?show=all              every post, including the daily AI roundups
 * Without a filter the writing page lists the essays (everything except the AI roundups).
 * The "Arguments for God" series tag lists its parts in order, oldest first.
 */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_home() || $q->is_search() || $q->is_archive() ) {
		$q->set( 'posts_per_page', 12 );
	}
	if ( $q->is_search() ) {
		$q->set( 'post_type', 'post' );
	}
	if ( $q->is_tag( AMK_SERIES_TAG ) ) {
		$q->set( 'orderby', 'date' );
		$q->set( 'order', 'ASC' );
	}
	if ( ! $q->is_home() ) {
		return;
	}
	$theme = isset( $_GET['theme'] ) ? sanitize_key( wp_unslash( $_GET['theme'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$cats  = amk_theme_category_ids( $theme );
	if ( $cats ) {
		$q->set( 'category__in', $cats );
	} elseif ( 'all' !== amk_writing_show() && amk_theme_category_ids( 'ai' ) ) {
		$q->set( 'category__not_in', amk_theme_category_ids( 'ai' ) );
	}
	if ( 'ai' === $theme ) {
		$kind = isset( $_GET['kind'] ) ? sanitize_key( wp_unslash( $_GET['kind'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$pod  = get_term_by( 'slug', 'podcast', 'post_tag' );
		if ( 'podcasts' === $kind ) {
			$q->set( 'tag', 'podcast' );
		} elseif ( 'roundups' === $kind && $pod ) {
			$q->set( 'tag__not_in', array( $pod->term_id ) );
		}
	}
} );

// Clear the cached search index and counts when posts change.
add_action( 'save_post_post', 'amk_flush_caches' );
add_action( 'deleted_post', 'amk_flush_caches' );
add_action( 'set_object_terms', 'amk_flush_caches' );
function amk_flush_caches() {
	delete_transient( 'amk_search_index' );
	delete_transient( 'amk_series_ids' );
}
