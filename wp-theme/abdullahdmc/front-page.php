<?php
/**
 * Home page.
 */
defined( 'ABSPATH' ) || exit;

$amk_ai_ids  = amk_theme_category_ids( 'ai' );
$amk_series  = new WP_Query( array( 'tag' => 'arguments-for-god', 'posts_per_page' => 4, 'ignore_sticky_posts' => true ) );
$amk_latest  = new WP_Query( array( 'posts_per_page' => 8, 'category__not_in' => $amk_ai_ids, 'ignore_sticky_posts' => true ) );
$amk_ai      = $amk_ai_ids ? new WP_Query( array( 'category__in' => $amk_ai_ids, 'posts_per_page' => 3 ) ) : null;
$amk_feature = $amk_series->have_posts() ? $amk_series->posts[0] : ( $amk_latest->posts[0] ?? null );
$amk_count   = (int) wp_count_posts()->publish;

amk_head();
amk_header( 'home' );
?>
<section class="hero"><div class="wrap hero-grid">
	<div>
		<span class="eyebrow">Physician · Researcher · Thinker</span>
		<h1>Evidence for health,<br><em>reflection</em> for life.</h1>
		<p class="lede">I am an epidemiologist, medical doctor and researcher, currently focusing on AI-based prognostic and diagnostic modelling for infectious disease management. Here you will find my writing on science, philosophy, theology, research and life, in both Bangla and English.</p>
		<a class="btn btn-consult" href="<?php echo esc_url( amk_page_url( 'consultation' ) ); ?>"><span class="pulse" aria-hidden="true"></span>Contact for consultation →</a>
		<div class="hero-actions">
			<a class="btn btn-primary" href="<?php echo esc_url( amk_writing_url() ); ?>">লেখাগুলো পড়ুন →</a>
			<a class="btn btn-ghost" href="<?php echo esc_url( amk_page_url( 'research' ) ); ?>">Research &amp; publications</a>
		</div>
		<div class="stats">
			<div class="stat"><b id="pub-count">100+</b><span>peer-reviewed publications</span></div>
			<div class="stat"><b>300+</b><span>research projects analysed</span></div>
			<div class="stat"><b><?php echo esc_html( $amk_count ); ?></b><span>essays, poems &amp; reviews</span></div>
		</div>
	</div>
	<div class="hero-side">
		<div class="hero-visual">
			<div class="portrait photo" style="background-image:url(<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/portrait.jpg' ); ?>)" role="img" aria-label="Dr. Md Abdullah Saeed Khan speaking at a conference"></div>
			<div class="badge"><b>DPhil, University of Oxford</b>Machine-learning tools for febrile illness with MORU &amp; SEACTN</div>
		</div>
		<a class="scholar-card" id="scholar-card" href="<?php echo esc_url( amk_page_url( 'research' ) ); ?>" aria-label="Google Scholar citation metrics"></a>
	</div>
</div></section>

<?php if ( $amk_feature ) : $amk_ft = amk_themes()[ amk_theme_of( $amk_feature ) ]; ?>
<section class="section" style="padding-top:32px"><div class="wrap">
	<div class="section-head">
		<div><span class="eyebrow">সাম্প্রতিক লেখা · Latest writing</span><h2>নতুন লেখা</h2></div>
		<a class="link-more" href="<?php echo esc_url( amk_writing_url() ); ?>">সব লেখা দেখুন →</a>
	</div>
	<a class="feature" href="<?php echo esc_url( get_permalink( $amk_feature ) ); ?>" style="--c1:<?php echo esc_attr( $amk_ft['c1'] ); ?>">
		<?php echo amk_cover( $amk_feature, 'amk-cover', 'lg' ); // phpcs:ignore ?>
		<div class="f-body"><div class="card-meta"><span class="dot"></span>Featured · <?php echo esc_html( $amk_ft['bn'] . ' · ' . amk_date( $amk_feature ) ); ?></div>
			<h3><?php echo esc_html( get_the_title( $amk_feature ) ); ?></h3>
			<p><?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $amk_feature ) ) ); ?></p><span class="link-more">পড়ুন →</span></div>
	</a>
	<div class="grid">
		<?php
		$amk_n = 0;
		foreach ( $amk_latest->posts as $amk_p ) {
			if ( $amk_p->ID === $amk_feature->ID || $amk_n >= 6 ) {
				continue;
			}
			amk_card( $amk_p );
			$amk_n++;
		}
		?>
	</div>
</div></section>
<?php endif; ?>

<?php if ( $amk_ai && $amk_ai->have_posts() ) : ?>
<section class="section" style="padding-top:0"><div class="wrap">
	<div class="ai-strip">
		<div class="section-head" style="margin-bottom:0">
			<div><span class="eyebrow">Updated daily</span><h2>AI in Health Research</h2>
				<p style="margin:8px 0 0;opacity:.8;max-width:40em">A daily roundup of new studies on large language models, machine learning and prediction models in medicine.</p></div>
			<a class="link-more" href="<?php echo esc_url( amk_writing_url( array( 'theme' => 'ai' ) ) ); ?>">All roundups →</a>
		</div>
		<div class="ai-list">
			<?php foreach ( $amk_ai->posts as $amk_p ) : ?>
				<a href="<?php echo esc_url( get_permalink( $amk_p ) ); ?>"><span class="d"><?php echo esc_html( strtoupper( get_the_date( 'j M Y', $amk_p ) ) ); ?></span><p><?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $amk_p ) ) ); ?></p></a>
			<?php endforeach; ?>
		</div>
	</div>
</div></section>
<?php endif; ?>

<?php if ( count( $amk_series->posts ) > 1 ) : ?>
<section class="section" style="padding-top:24px"><div class="wrap">
	<div class="section-head">
		<div><span class="eyebrow">ধারাবাহিক · Series</span><h2>স্রষ্টার অস্তিত্বের যুক্তি</h2>
			<p style="margin:8px 0 0;color:var(--ink-2)">অ্যারিস্টটল, আকুইনাস, ইবনে সিনা থেকে লাইবনিজ: দর্শনের ইতিহাসের প্রধান যুক্তিগুলো, প্রতি সপ্তাহে নতুন পর্ব।</p></div>
		<a class="link-more" href="<?php echo esc_url( get_term_link( 'arguments-for-god', 'post_tag' ) ); ?>">পুরো সিরিজ →</a>
	</div>
	<div class="grid"><?php foreach ( array_slice( $amk_series->posts, 1, 3 ) as $amk_p ) { amk_card( $amk_p ); } ?></div>
</div></section>
<?php endif; ?>

<section class="section" style="padding-top:24px"><div class="wrap">
	<div class="section-head"><div><span class="eyebrow">বিষয়ভিত্তিক · Explore by theme</span><h2>যা নিয়ে লিখি</h2></div></div>
	<div class="themes">
		<?php foreach ( amk_themes() as $amk_key => $amk_t ) : ?>
			<?php if ( ! amk_theme_category_ids( $amk_key ) ) { continue; } ?>
			<a class="theme-tile" data-theme="<?php echo esc_attr( $amk_key ); ?>" href="<?php echo esc_url( amk_writing_url( array( 'theme' => $amk_key ) ) ); ?>" style="--c1:<?php echo esc_attr( $amk_t['c1'] ); ?>;--c2:<?php echo esc_attr( $amk_t['c2'] ); ?>">
				<b><?php echo esc_html( $amk_t['bn'] ); ?></b><span><?php echo esc_html( $amk_t['en'] ); ?></span></a>
		<?php endforeach; ?>
	</div>
</div></section>

<section class="section band" id="research"><div class="wrap research">
	<div class="intro">
		<span class="eyebrow">Research</span>
		<h2 style="font-size:clamp(26px,3.2vw,34px);margin:10px 0 14px">Infectious disease, outcomes &amp; health systems</h2>
		<p>My work spans tetanus, dengue, COVID-19, febrile illness and health-system accountability: clinical studies, quality of life, economic evaluation and prediction models.</p>
		<div class="affil"><span>University of Oxford</span><span>MORU · SEACTN</span><span>MoHFW Bangladesh</span><span>Pi Research Development Center</span></div>
		<p style="margin-top:22px"><a class="link-more" href="<?php echo esc_url( amk_page_url( 'research' ) ); ?>">Metrics, projects &amp; all publications →</a></p>
	</div>
	<div>
		<span class="eyebrow">Recent publications</span>
		<ul class="pubs" id="recent-pubs" style="margin-top:12px"></ul>
	</div>
</div></section>

<section class="section"><div class="wrap">
	<div class="section-head"><div><span class="eyebrow">Find me online</span><h2>Profiles &amp; channels</h2></div></div>
	<div class="yt" id="yt"></div>
	<div class="links-row" id="all-links" style="margin-top:16px"></div>
</div></section>
<?php
wp_reset_postdata();
amk_footer();
