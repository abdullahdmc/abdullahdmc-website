<?php
/**
 * Search index for the header's quick search: every published post's title, excerpt, date, link
 * and a few keywords (English topic words, the slug, and Latin spellings of names written in Bangla),
 * so "aristotle" finds অ্যারিস্টটল. Cached until a post is saved or deleted.
 */

defined( 'ABSPATH' ) || exit;

function amk_search_keywords( $post ) {
	$topic = array(
		'faith'      => 'faith philosophy religion god',
		'health'     => 'medicine health research',
		'science'    => 'science evolution biology',
		'mind'       => 'mind brain consciousness',
		'literature' => 'literature poems stories reviews',
		'essays'     => 'essays reflections life',
		'ai'         => 'ai artificial intelligence health research roundup machine learning',
	);
	$names = array(
		'/অ্যারিস্টটল|এরিস্টটল/u' => 'aristotle এরিস্টটল আরিস্টটল',
		'/ইবনে সিনা|ইবন সিনা/u'   => 'ibn sina avicenna ইবন সিনা ইবনে সীনা',
		'/ফেজার|ফিজার/u'          => 'feser',
		'/অ্যাকুইনাস|আকুইনাস/u'    => 'aquinas একুইনাস আকুইনাস',
		'/অগাস্টিন/u'             => 'augustine',
		'/প্লেটো/u'               => 'plato plotinus',
		'/লাইবনিজ/u'              => 'leibniz',
		'/বিবর্তন/u'               => 'evolution',
	);
	$text  = $post->post_title . ' ' . wp_strip_all_tags( $post->post_content );
	$words = array( $topic[ amk_theme_of( $post ) ] ?? '', str_replace( '-', ' ', urldecode( $post->post_name ) ) );
	foreach ( $names as $pattern => $alias ) {
		if ( preg_match( $pattern, $text ) ) {
			$words[] = $alias;
		}
	}
	return trim( implode( ' ', $words ) );
}

function amk_search_index() {
	$index = get_transient( 'amk_search_index' );
	if ( false !== $index ) {
		return $index;
	}
	$index = array();
	$posts = get_posts( array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	foreach ( $posts as $post ) {
		$index[] = array(
			'title'    => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'excerpt'  => wp_trim_words( wp_strip_all_tags( $post->post_excerpt ? $post->post_excerpt : $post->post_content ), 30, '…' ),
			'keywords' => amk_search_keywords( $post ),
			'url'      => get_permalink( $post ),
			'date'     => get_the_date( 'Y-m-d', $post ),
		);
	}
	set_transient( 'amk_search_index', $index, DAY_IN_SECONDS );
	return $index;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'amk/v1', '/search-index', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'callback'            => function () {
			$response = rest_ensure_response( amk_search_index() );
			$response->header( 'Cache-Control', 'public, max-age=600' );
			return $response;
		},
	) );
} );
