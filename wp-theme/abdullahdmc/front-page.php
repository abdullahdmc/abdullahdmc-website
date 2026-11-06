<?php
/**
 * Home page.
 */
defined( 'ABSPATH' ) || exit;

$amk_ai_ids  = amk_theme_category_ids( 'ai' );
$amk_latest  = get_posts( array( 'posts_per_page' => 4, 'category__not_in' => $amk_ai_ids, 'ignore_sticky_posts' => true ) );
$amk_pod_tag = get_term_by( 'slug', 'podcast', 'post_tag' );
// AI band: the newest podcast episode (if any) leads, followed by the latest daily roundups.
$amk_pod     = $amk_ai_ids && $amk_pod_tag ? get_posts( array( 'category__in' => $amk_ai_ids, 'tag' => 'podcast', 'posts_per_page' => 1 ) ) : array();
$amk_ai      = $amk_ai_ids ? get_posts( array( 'category__in' => $amk_ai_ids, 'tag__not_in' => $amk_pod_tag ? array( $amk_pod_tag->term_id ) : array(), 'posts_per_page' => $amk_pod ? 2 : 3 ) ) : array();
$amk_series  = amk_series_ids();
// "Essays, poems & reviews": the author's own writing, so the daily AI roundups are not counted.
$amk_count   = ( new WP_Query( array( 'category__not_in' => $amk_ai_ids, 'posts_per_page' => 1, 'fields' => 'ids' ) ) )->found_posts;
$amk_p       = amk_profile();
$amk_r       = amk_research();
$amk_scholar = $amk_r['scholar'] ?? array();
$amk_years   = array_slice( (array) ( $amk_scholar['per_year'] ?? array() ), -6, null, true );
$amk_max     = $amk_years ? max( 1, max( $amk_years ) ) : 1;
$amk_arrow   = amk_arrow();

/** A story on the home page: the first is the large feature. */
function amk_home_story( $post, $feature ) {
	$url    = get_permalink( $post );
	$bangla = amk_post_is_bangla( $post );
	$lang   = $bangla ? ' lang="bn"' : '';
	$theme  = amk_themes()[ amk_theme_of( $post ) ]['bn'];
	$sizes  = $feature ? '(max-width:800px) calc(100vw - 40px), 600px' : '(max-width:560px) 100px, 140px';
	?>
	<article class="<?php echo $feature ? 'feature-story' : 'small-story'; ?>">
		<a class="story-image" href="<?php echo esc_url( $url ); ?>" tabindex="-1" aria-hidden="true"><?php echo $feature ? amk_post_image( $post, 'amk-cover', $sizes ) : amk_motif_tile( $post ); // phpcs:ignore ?></a>
		<div class="story-copy">
			<div class="meta"><span<?php echo amk_is_bangla( $theme ) ? ' lang="bn"' : ''; ?>><?php echo esc_html( $theme ); ?></span><time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $post ) ); ?>"<?php echo $lang; // phpcs:ignore ?>><?php echo esc_html( amk_date( $post ) ); ?></time></div>
			<h3<?php echo $lang; // phpcs:ignore ?>><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
			<p<?php echo $lang; // phpcs:ignore ?>><?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $post ) ) ); ?></p>
			<a class="text-link" href="<?php echo esc_url( $url ); ?>"<?php echo $lang; // phpcs:ignore ?>><?php echo $bangla ? ( $feature ? 'সম্পূর্ণ লেখা পড়ুন' : 'পড়ুন' ) : 'Read article'; ?> <?php echo amk_arrow(); // phpcs:ignore ?></a>
		</div>
	</article>
	<?php
}

amk_head();
amk_header( 'home' );
?>
<section class="hero" id="top" aria-labelledby="hero-title">
	<img class="hero-image" src="<?php echo esc_url( amk_img( 'hero-balanced.webp' ) ); ?>" width="1983" height="793" fetchpriority="high" alt="Dr. Md Abdullah Saeed Khan speaking at a conference">
	<div class="wrap hero-inner"><div class="hero-copy">
		<p class="identity">PHYSICIAN · RESEARCHER · THINKER</p>
		<h1 id="hero-title">Evidence for health,<br><em>reflection</em> for life.</h1>
		<p class="hero-bio">I am an epidemiologist, medical doctor and researcher, currently focusing on AI-based prognostic and diagnostic modelling for infectious disease management. Here you will find my writing on science, philosophy, theology, research and life, in both Bangla and English.</p>
		<div class="hero-actions">
			<a class="button gold" href="<?php echo esc_url( amk_writing_url() ); ?>" lang="bn">লেখাগুলো পড়ুন <?php echo $amk_arrow; // phpcs:ignore ?></a>
			<a class="button outline" href="<?php echo esc_url( amk_page_url( 'research' ) ); ?>">Research &amp; publications</a>
			<a class="consult" href="<?php echo esc_url( amk_page_url( 'consultation' ) ); ?>">Contact for consultation <?php echo $amk_arrow; // phpcs:ignore ?></a>
		</div>
	</div></div>
</section>

<section class="profile-section wrap" id="research-profile" aria-labelledby="profile-title">
	<div class="profile-top">
		<div class="profile-copy">
			<h2 id="profile-title">DPhil, University of Oxford</h2>
			<p class="profile-roles">Assistant Professor (Epidemiology)<br>Health Research &amp; Data Analytics Consultant</p>
			<p>Machine-learning tools for febrile illness with MORU &amp; SEACTN</p>
			<a class="text-link" href="<?php echo esc_url( amk_page_url( 'research' ) . '#projects' ); ?>">Current projects and funding <?php echo $amk_arrow; // phpcs:ignore ?></a>
		</div>
		<svg class="oxford oxford-sketch" viewBox="0 0 1774 887" role="img" aria-labelledby="oxford-art-title"><title id="oxford-art-title">An architectural illustration of Oxford’s Radcliffe Camera</title><defs><mask id="oxford-brush-mask" maskUnits="userSpaceOnUse" x="0" y="0" width="1774" height="887"><rect class="sketch-base" width="1774" height="887" fill="white"/><g fill="none" stroke="white" stroke-width="145" stroke-linecap="round"><?php
		for ( $amk_i = 0; $amk_i < 10; $amk_i++ ) {
			$amk_y = 25 + $amk_i * 95;
			$amk_d = 0 === $amk_i % 2 ? sprintf( 'M -80 %d Q 880 %d 1854 %d', $amk_y, $amk_y - 35, $amk_y + 15 ) : sprintf( 'M 1854 %d Q 880 %d -80 %d', $amk_y, $amk_y + 35, $amk_y + 15 );
			printf( '<path class="brush-stroke" d="%s" pathLength="1" style="--stroke-delay:%.2fs"/>', esc_attr( $amk_d ), $amk_i * 0.15 );
		}
		?></g></mask></defs><image href="<?php echo esc_url( amk_img( 'oxford-1200.webp' ) ); ?>" width="1774" height="887" mask="url(#oxford-brush-mask)"/></svg>
	</div>
	<div class="profile-bottom">
		<dl class="stats">
			<?php if ( ! empty( $amk_r['publications'] ) ) : ?><div><dt>Publications, including preprints</dt><dd><?php echo esc_html( count( $amk_r['publications'] ) ); ?></dd></div><?php endif; ?>
			<div><dt>Research projects analysed</dt><dd>300<span>+</span></dd></div>
			<div><dt>Essays, poems &amp; reviews</dt><dd><?php echo esc_html( $amk_count ); ?></dd></div>
		</dl>
		<?php if ( ! empty( $amk_scholar['url'] ) ) : ?>
		<div class="scholar">
			<div class="scholar-heading"><a href="<?php echo esc_url( $amk_scholar['url'] ); ?>"><?php echo amk_icon( 'scholar' ); // phpcs:ignore ?> Google Scholar</a><?php if ( ! empty( $amk_r['updated'] ) ) : ?><span>Updated <?php echo esc_html( wp_date( 'j M Y', strtotime( $amk_r['updated'] . ' 12:00:00' ) ) ); ?></span><?php endif; ?></div>
			<div class="scholar-data">
				<table><caption class="sr-only">Google Scholar metrics</caption><tbody>
					<tr><th scope="row">Citations</th><td><?php echo esc_html( number_format( (int) ( $amk_scholar['citations'] ?? 0 ) ) ); ?></td></tr>
					<tr><th scope="row">h-index</th><td><?php echo esc_html( $amk_scholar['h_index'] ?? '' ); ?></td></tr>
					<tr><th scope="row">i10-index</th><td><?php echo esc_html( $amk_scholar['i10_index'] ?? '' ); ?></td></tr>
				</tbody></table>
				<?php if ( $amk_years ) : ?>
				<figure class="chart"><figcaption class="sr-only">Citations by year: <?php echo esc_html( implode( '; ', array_map( function ( $y, $n ) { return $y . ': ' . $n; }, array_keys( $amk_years ), $amk_years ) ) ); ?>.</figcaption>
					<div class="bars" aria-hidden="true"><?php foreach ( $amk_years as $amk_y => $amk_n ) : ?><div title="<?php echo esc_attr( $amk_y . ': ' . number_format( (int) $amk_n ) . ' citations' ); ?>"><span style="height:<?php echo esc_attr( max( 3, round( $amk_n / $amk_max * 70 ) ) ); ?>px"></span><small><?php echo esc_html( substr( (string) $amk_y, 2 ) ); ?></small></div><?php endforeach; ?></div>
				</figure>
				<?php endif; ?>
			</div>
			<div class="scholar-links"><a href="<?php echo esc_url( $amk_scholar['url'] ); ?>">Google Scholar <?php echo $amk_arrow; // phpcs:ignore ?></a><?php if ( ! empty( $amk_r['researchgate']['url'] ) ) : ?><a href="<?php echo esc_url( $amk_r['researchgate']['url'] ); ?>">ResearchGate <?php echo $amk_arrow; // phpcs:ignore ?></a><?php endif; ?></div>
		</div>
		<?php endif; ?>
	</div>
</section>

<?php if ( $amk_latest ) : ?>
<section class="section wrap" id="writing" aria-labelledby="writing-title">
	<div class="section-heading"><div><h2 id="writing-title" lang="bn">নতুন লেখা</h2><p>সাম্প্রতিক লেখা · Latest writing</p></div><a class="text-link" href="<?php echo esc_url( amk_writing_url() ); ?>" lang="bn">সব লেখা দেখুন <?php echo $amk_arrow; // phpcs:ignore ?></a></div>
	<div class="writing-grid">
		<?php amk_home_story( $amk_latest[0], true ); ?>
		<?php if ( count( $amk_latest ) > 1 ) : ?><div class="story-list"><?php foreach ( array_slice( $amk_latest, 1 ) as $amk_post ) { amk_home_story( $amk_post, false ); } ?></div><?php endif; ?>
	</div>
</section>
<?php endif; ?>

<?php if ( $amk_ai || $amk_pod ) : ?>
<section class="roundup-band" aria-labelledby="ai-title"><div class="wrap roundup">
	<div class="roundup-intro"><h2 id="ai-title">AI in Health Research</h2><p>A daily roundup of new studies on large language models, machine learning and prediction models in medicine.</p><a class="text-link" href="<?php echo esc_url( amk_writing_url( array( 'theme' => 'ai' ) ) ); ?>">All roundups and episodes <?php echo $amk_arrow; // phpcs:ignore ?></a></div>
	<?php foreach ( $amk_pod as $amk_post ) : $amk_pd = amk_podcast( $amk_post ); ?>
		<article><time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $amk_post ) ); ?>">Podcast<?php echo $amk_pd[1] ? ' · ' . esc_html( $amk_pd[1] ) : ''; ?></time><p class="roundup-excerpt"><?php echo esc_html( get_the_title( $amk_post ) ); ?></p><?php if ( $amk_pd[0] ) : ?><audio controls preload="none" src="<?php echo esc_url( $amk_pd[0] ); ?>" aria-label="<?php echo esc_attr( get_the_title( $amk_post ) ); ?>"></audio><?php endif; ?><a class="text-link" href="<?php echo esc_url( get_permalink( $amk_post ) ); ?>">Episode notes <?php echo $amk_arrow; // phpcs:ignore ?></a></article>
	<?php endforeach; ?>
	<?php foreach ( $amk_ai as $amk_post ) : ?>
		<article><time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d', $amk_post ) ); ?>"><?php echo esc_html( get_the_date( 'j M Y', $amk_post ) ); ?></time><p class="roundup-excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $amk_post ) ) ); ?></p><a class="text-link" href="<?php echo esc_url( get_permalink( $amk_post ) ); ?>">Read roundup <?php echo $amk_arrow; // phpcs:ignore ?></a></article>
	<?php endforeach; ?>
</div></section>
<?php endif; ?>

<?php if ( count( $amk_series ) > 1 ) : ?>
<section class="series-section" aria-labelledby="series-title"><div class="wrap">
	<div class="section-heading"><div><p class="label">ধারাবাহিক · Series</p><h2 id="series-title" lang="bn">স্রষ্টার অস্তিত্বের যুক্তি</h2><p lang="bn">অ্যারিস্টটল, আকুইনাস, ইবনে সিনা থেকে লাইবনিজ: দর্শনের ইতিহাসের প্রধান যুক্তিগুলো, প্রতি সপ্তাহে নতুন পর্ব।</p></div><a class="text-link" href="<?php echo esc_url( amk_series_url() ); ?>" lang="bn">পুরো সিরিজ <?php echo $amk_arrow; // phpcs:ignore ?></a></div>
	<div class="series-grid">
		<?php foreach ( array_slice( $amk_series, 0, 3 ) as $amk_i => $amk_id ) : $amk_title = get_the_title( $amk_id ); ?>
			<?php // The cover's drawing only: the cover image itself repeats the title the card prints. ?><article class="series-card"><?php echo amk_motif_svg( $amk_id ); // phpcs:ignore WordPress.Security.EscapeOutput -- theme's own SVG ?><div><span class="label" lang="bn">পর্ব <?php echo esc_html( amk_bn_digits( $amk_i + 1 ) . ' / ' . amk_bn_digits( count( $amk_series ) ) ); ?></span><h3 lang="bn"><a href="<?php echo esc_url( get_permalink( $amk_id ) ); ?>"><?php echo esc_html( $amk_title ); ?></a></h3><a class="series-link" href="<?php echo esc_url( get_permalink( $amk_id ) ); ?>" aria-label="<?php echo esc_attr( 'Read ' . $amk_title ); ?>"><?php echo $amk_arrow; // phpcs:ignore ?></a></div></article>
		<?php endforeach; ?>
	</div>
</div></section>
<?php endif; ?>

<section class="section wrap topics-section" aria-labelledby="topics-title">
	<div class="section-heading"><div><h2 id="topics-title" lang="bn">যা নিয়ে লিখি</h2><p>বিষয়ভিত্তিক · Explore by theme</p></div></div>
	<div class="topics">
		<?php foreach ( amk_themes() as $amk_key => $amk_t ) : ?>
			<?php if ( ! amk_theme_category_ids( $amk_key ) ) { continue; } ?>
			<a href="<?php echo esc_url( amk_writing_url( array( 'theme' => $amk_key ) ) ); ?>"><?php echo amk_icon( $amk_t['icon'] ); // phpcs:ignore ?><strong<?php echo amk_is_bangla( $amk_t['bn'] ) ? ' lang="bn"' : ''; ?>><?php echo esc_html( $amk_t['bn'] ); ?></strong><span><?php echo esc_html( $amk_t['en'] ); ?></span><?php echo $amk_arrow; // phpcs:ignore ?></a>
		<?php endforeach; ?>
	</div>
</section>

<?php if ( ! empty( $amk_r['publications'] ) ) : ?>
<section class="wrap research-section" id="research" aria-labelledby="research-title">
	<div class="section-heading"><div><h2 id="research-title">Infectious disease, outcomes &amp; health systems</h2><p>My work spans tetanus, dengue, COVID-19, febrile illness and health-system accountability: clinical studies, quality of life, economic evaluation and prediction models.</p></div><a class="text-link" href="<?php echo esc_url( amk_page_url( 'research' ) ); ?>">Metrics, projects &amp; all publications <?php echo $amk_arrow; // phpcs:ignore ?></a></div>
	<div class="publications">
		<?php foreach ( array_slice( $amk_r['publications'], 0, 4 ) as $amk_i => $amk_pub ) : ?>
			<article><span class="publication-number"><?php echo esc_html( str_pad( $amk_i + 1, 2, '0', STR_PAD_LEFT ) ); ?></span><h3><a href="<?php echo esc_url( $amk_pub['link'] ?? '' ); ?>"><?php echo esc_html( $amk_pub['title'] ?? '' ); ?></a></h3><p><?php echo esc_html( $amk_pub['venue'] ?? '' ); ?></p><time><?php echo esc_html( $amk_pub['year'] ?? '' ); ?></time><a class="circle-link" href="<?php echo esc_url( $amk_pub['link'] ?? '' ); ?>" aria-label="<?php echo esc_attr( 'Read publication: ' . ( $amk_pub['title'] ?? '' ) ); ?>"><?php echo $amk_arrow; // phpcs:ignore ?></a></article>
		<?php endforeach; ?>
	</div>
	<div class="affiliations"><span>University of Oxford</span><span>MORU · SEACTN</span><span>MoHFW Bangladesh</span><span>Pi Research Development Center</span></div>
</section>
<?php endif; ?>

<?php $amk_youtube = $amk_p['links']['youtube'] ?? array(); $amk_links = array_merge( $amk_p['links']['research'] ?? array(), $amk_p['links']['social'] ?? array() ); ?>
<?php if ( $amk_youtube || $amk_links ) : ?>
<section class="section wrap" aria-labelledby="channels-title">
	<div class="section-heading"><div><h2 id="channels-title">Profiles &amp; channels</h2><p>Find me online</p></div></div>
	<?php if ( $amk_youtube ) : ?>
	<div class="channels">
		<?php foreach ( $amk_youtube as $amk_i => $amk_ch ) : $amk_cover = $amk_i ? 'research-notes-cover' : 'truth-quest-cover'; ?>
			<a class="channel" href="<?php echo esc_url( $amk_ch[1] ?? '' ); ?>"><div class="channel-image channel-cover"><img src="<?php echo esc_url( amk_img( $amk_cover . '.webp' ) ); ?>" srcset="<?php echo esc_attr( amk_img( $amk_cover . '-480.webp' ) . ' 480w, ' . amk_img( $amk_cover . '.webp' ) . ' 900w' ); ?>" sizes="(max-width:560px) calc(50vw - 44px), (max-width:800px) 45vw, 260px" alt="<?php echo esc_attr( ( $amk_ch[0] ?? '' ) . ' channel artwork' ); ?>" width="400" height="300" loading="lazy"><span class="play"><?php echo amk_icon( 'play' ); // phpcs:ignore ?></span></div><div><span class="label">YOUTUBE CHANNEL</span><h3><?php echo esc_html( $amk_ch[0] ?? '' ); ?></h3><p<?php echo amk_is_bangla( $amk_ch[3] ?? '' ) ? ' lang="bn"' : ''; ?>><?php echo esc_html( $amk_ch[3] ?? '' ); ?></p><span class="text-link">Watch on YouTube <?php echo $amk_arrow; // phpcs:ignore ?></span></div></a>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
	<?php amk_profile_links( $amk_links, true ); ?>
</section>
<?php endif; ?>
<?php
amk_footer();
