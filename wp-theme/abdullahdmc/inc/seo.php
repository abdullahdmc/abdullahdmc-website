<?php
/**
 * Search and sharing: page titles and descriptions, social preview image, Person schema,
 * Bangla language tags, redirects from retired pages, and keeping thin pages out of the sitemap.
 * Works with Yoast SEO; anything Yoast already has a custom value for is left alone.
 */

defined( 'ABSPATH' ) || exit;

const AMK_NAME = 'Md Abdullah Saeed Khan';

// Retired pages and where they now live (permanent redirects).
function amk_redirects() {
	return array(
		'/short-biography/publications' => amk_page_url( 'research' ) . '#publications',
		'/short-biography'              => amk_page_url( 'biography' ),
		'/sitemap'                      => amk_writing_url(),
	);
}

/** Title and description for the site's own pages; null when WordPress/Yoast should decide. */
function amk_page_seo() {
	if ( is_front_page() ) {
		return array(
			'Dr. ' . AMK_NAME . ' · Epidemiologist, Physician & Researcher',
			'Epidemiologist and medical doctor working on AI-based prognostic and diagnostic models for infectious diseases. Research, publications, and writing on science, philosophy, faith and life in Bangla and English.',
		);
	}
	if ( is_home() ) {
		$theme = isset( $_GET['theme'] ) ? ( amk_themes()[ sanitize_key( wp_unslash( $_GET['theme'] ) ) ] ?? null ) : null;
		return array(
			( $theme ? $theme['bn'] . ' · ' : '' ) . 'লেখা · Writing · ' . AMK_NAME,
			'বিবর্তন, মস্তিষ্ক, চিকিৎসা, গবেষণা, বিশ্বাস ও দর্শন নিয়ে প্রবন্ধ, কবিতা, গল্প ও বইয়ের রিভিউ। Essays, poems and reviews by ' . AMK_NAME . '.',
		);
	}
	$template = is_page() ? get_page_template_slug() : '';
	$pages    = array(
		'templates/template-research.php'     => array(
			'Research & Publications · Dr. ' . AMK_NAME,
			'Publications, Google Scholar citation metrics, current projects and research funding of Dr. ' . AMK_NAME . ': tetanus, dengue, COVID-19, febrile illness, mental health and machine-learning prediction models.',
		),
		'templates/template-biography.php'    => array(
			'Biography · Dr. ' . AMK_NAME . ', Epidemiologist',
			'Dr. ' . AMK_NAME . ': Assistant Professor (Epidemiology), DPhil fellow in Clinical Medicine at the University of Oxford (MORU). Education, affiliations, experience, training and awards.',
		),
		'templates/template-consultation.php' => array(
			'Health Research Consultation: Proposals, Data Analysis & Manuscripts · Dr. ' . AMK_NAME,
			'Independent consultation on health research proposal development, statistical data analysis (R, Stata, SPSS, Python), manuscript writing and review. Email for fee details.',
		),
	);
	return $pages[ $template ] ?? null;
}

add_filter( 'wpseo_title', function ( $title ) {
	$seo = amk_page_seo();
	if ( $seo ) {
		return $seo[0];
	}
	// Posts: add the author's name after the post title, unless an SEO title was set by hand.
	if ( is_singular( 'post' ) && ! get_post_meta( get_the_ID(), '_yoast_wpseo_title', true ) && false === strpos( $title, AMK_NAME ) ) {
		return wp_strip_all_tags( get_the_title() ) . ' · ' . AMK_NAME;
	}
	return $title;
} );

function amk_description( $desc ) {
	$seo = amk_page_seo();
	if ( $seo ) {
		return $seo[1];
	}
	if ( is_singular( 'post' ) && ! $desc ) {
		return wp_trim_words( wp_strip_all_tags( get_the_excerpt() ), 30, '…' );
	}
	return $desc;
}
add_filter( 'wpseo_metadesc', 'amk_description' );
add_filter( 'wpseo_opengraph_desc', 'amk_description' );
add_filter( 'wpseo_opengraph_title', function ( $title ) {
	$seo = amk_page_seo();
	return $seo ? $seo[0] : $title;
} );

// Social preview image: the post's cover, otherwise the site's default image (option amk_og_image).
add_action( 'wpseo_add_opengraph_images', function ( $images ) {
	if ( method_exists( $images, 'has_images' ) && $images->has_images() ) {
		return;
	}
	// Yoast can miss a cover attached after the post was first saved; use it directly.
	if ( is_singular() && has_post_thumbnail() ) {
		$images->add_image_by_id( get_post_thumbnail_id() );
		return;
	}
	$default = (int) get_option( 'amk_og_image' );
	if ( $default ) {
		$images->add_image_by_id( $default );
	}
} );

// Bangla posts are tagged as Bangla for search engines and social sites.
function amk_is_bangla_page() {
	return is_singular( 'post' ) && amk_is_bangla( get_the_title() );
}
add_filter( 'language_attributes', function ( $output ) {
	return amk_is_bangla_page() ? preg_replace( '/lang="[^"]*"/', 'lang="bn-BD"', $output ) : $output;
} );
add_filter( 'wpseo_locale', function ( $locale ) {
	return amk_is_bangla_page() ? 'bn_BD' : $locale;
} );

// Describe the author as a Person (name, roles, affiliations, profiles) and credit posts to him.
add_filter( 'wpseo_schema_graph', function ( $graph ) {
	$profile = json_decode( (string) get_option( 'amk_profile' ), true );
	if ( ! $profile ) {
		return $graph;
	}
	$id     = home_url( '/#person' );
	$links  = array_merge( $profile['links']['research'] ?? array(), $profile['links']['social'] ?? array(), $profile['links']['youtube'] ?? array() );
	$person = array(
		'@type'       => 'Person',
		'@id'         => $id,
		'name'        => 'Dr. ' . AMK_NAME,
		'url'         => home_url( '/' ),
		'image'       => get_stylesheet_directory_uri() . '/assets/portrait.jpg',
		'jobTitle'    => 'Assistant Professor (Epidemiology)',
		'description' => 'Epidemiologist and medical doctor; DPhil fellow in Clinical Medicine, University of Oxford.',
		'email'       => 'mailto:abdullahdmc@gmail.com',
		'worksFor'    => array(
			array( '@type' => 'Organization', 'name' => 'Ministry of Health and Family Welfare, Bangladesh' ),
			array( '@type' => 'Organization', 'name' => 'Mahidol Oxford Tropical Medicine Research Unit (MORU)' ),
		),
		'alumniOf'    => array(
			array( '@type' => 'CollegeOrUniversity', 'name' => 'University of Oxford' ),
			array( '@type' => 'CollegeOrUniversity', 'name' => 'North South University' ),
			array( '@type' => 'CollegeOrUniversity', 'name' => 'Dhaka Medical College' ),
		),
		'knowsAbout'  => array( 'Epidemiology', 'Infectious diseases', 'Public health', 'Biostatistics', 'Machine learning in healthcare', 'Health research methods' ),
		'sameAs'      => array_values( array_map( function ( $l ) {
			return $l[1];
		}, $links ) ),
	);
	foreach ( $graph as &$node ) {
		$types = (array) ( $node['@type'] ?? array() );
		if ( in_array( 'Article', $types, true ) ) {
			$node['author'] = array( '@id' => $id );
		}
		if ( in_array( 'WebSite', $types, true ) ) {
			$node['publisher'] = array( '@id' => $id );
		}
		if ( in_array( 'WebPage', $types, true ) && ( is_front_page() || 'templates/template-biography.php' === get_page_template_slug() ) ) {
			$node['about'] = array( '@id' => $id );
		}
	}
	unset( $node );
	$graph[] = $person;
	return $graph;
} );

// Keep empty media pages and MailPoet's system pages out of search results and the sitemap.
add_filter( 'wpseo_sitemap_exclude_post_type', function ( $excluded, $post_type ) {
	return $excluded || in_array( $post_type, array( 'attachment', 'mailpoet_page' ), true );
}, 10, 2 );

add_action( 'template_redirect', function () {
	$path = untrailingslashit( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ) );
	$home = untrailingslashit( (string) wp_parse_url( home_url(), PHP_URL_PATH ) );
	$path = substr( $path, strlen( $home ) );
	foreach ( amk_redirects() as $from => $to ) {
		if ( $path === $from ) {
			wp_safe_redirect( $to, 301 );
			exit;
		}
	}
	// Media pages have no content of their own: send them to the image itself.
	if ( is_attachment() ) {
		wp_safe_redirect( wp_get_attachment_url( get_queried_object_id() ), 301 );
		exit;
	}
}, 1 );
add_filter( 'wpseo_robots', function ( $robots ) {
	return is_singular( 'mailpoet_page' ) ? 'noindex, follow' : $robots;
} );
