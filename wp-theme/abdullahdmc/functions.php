<?php
/**
 * Abdullah Saeed Khan: child theme of Astra.
 *
 * Every front-end template is provided here, so Astra's own header, footer and styles are not used.
 * Profile and research data are stored as JSON in two options (amk_profile, amk_research) that an
 * administrator can update through the REST API (/wp-json/wp/v2/settings); the theme only reads them.
 */

defined( 'ABSPATH' ) || exit;

define( 'AMK_VERSION', '1.0.5' );

require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/seo.php';

add_action( 'after_setup_theme', function () {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_image_size( 'amk-card', 800, 450, true );
	add_image_size( 'amk-cover', 1600, 900, true );
} );

// Profile and research data, editable by administrators over REST, readable by the theme.
add_action( 'init', function () {
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
	// MailPoet form shown in the home-page sign-up box (0 = no box).
	register_setting( 'general', 'amk_newsletter_form', array(
		'type'         => 'integer',
		'default'      => 0,
		'show_in_rest' => true,
	) );
	foreach ( array( 'amk_profile', 'amk_research' ) as $name ) {
		register_setting( 'general', $name, array(
			'type'         => 'string',
			'default'      => '',
			'show_in_rest' => true,
			'sanitize_callback' => function ( $value ) {
				return null === json_decode( (string) $value ) ? '' : (string) $value;
			},
		) );
	}
} );

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
	wp_enqueue_style( 'amk-fonts', 'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;1,9..144,500&family=Hind+Siliguri:wght@400;500;600&family=Inter:wght@400;500;600&family=Noto+Serif+Bengali:wght@500;600&display=swap', array(), null );
	wp_enqueue_style( 'amk-site', get_stylesheet_directory_uri() . '/assets/site.css', array( 'amk-fonts' ), AMK_VERSION );
	wp_enqueue_style( 'amk-wp', get_stylesheet_directory_uri() . '/assets/wp.css', array( 'amk-site' ), AMK_VERSION );
	if ( get_option( 'amk_css' ) ) {
		wp_add_inline_style( 'amk-wp', get_option( 'amk_css' ) );
	}
	wp_enqueue_script( 'amk-site', get_stylesheet_directory_uri() . '/assets/site.js', array(), AMK_VERSION, true );
	wp_add_inline_script( 'amk-site', amk_data_script(), 'before' );
	wp_enqueue_script( 'amk-pages', get_stylesheet_directory_uri() . '/assets/pages.js', array( 'amk-site' ), AMK_VERSION, true );
}, 100 );

// Astra's scroll-to-top button has no styling without Astra's CSS.
add_filter( 'astra_get_option_scroll-to-top-enable', '__return_false' );

add_filter( 'excerpt_length', function () {
	return 32;
} );
add_filter( 'excerpt_more', function () {
	return '…';
} );

// Writing page filters: ?theme=science limits the posts list to that theme's categories.
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_home() || $q->is_search() || $q->is_archive() ) {
		$q->set( 'posts_per_page', 12 );
	}
	if ( $q->is_home() && ! empty( $_GET['theme'] ) ) {
		$theme = sanitize_key( wp_unslash( $_GET['theme'] ) );
		$cats  = amk_theme_category_ids( $theme );
		if ( $cats ) {
			$q->set( 'category__in', $cats );
		}
	}
} );
