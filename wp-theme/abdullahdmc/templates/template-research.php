<?php
/**
 * Template Name: Research
 *
 * Metrics, projects, funding, publications and reviewing; filled from the amk_research and amk_profile options.
 */
defined( 'ABSPATH' ) || exit;
$amk_p       = amk_profile();
$amk_r       = amk_research();
$amk_pubs    = $amk_r['publications'] ?? array();
$amk_scholar = $amk_r['scholar'] ?? array();
amk_head();
amk_header( 'research' );
?>
<section class="research-hero"><div class="wrap">
	<?php amk_crumb( 'Research' ); ?>
	<div class="research-hero-grid">
		<div>
			<h1>Infectious disease,<br><em>outcomes</em> &amp; health data science</h1>
			<p class="lead">Clinical epidemiology of tetanus, dengue, COVID-19 and febrile illness; mental health and quality of life; health systems; and machine-learning prediction models. Global Burden of Disease collaborator.</p>
			<?php if ( $amk_pubs ) : ?><a class="button gold" href="#publications">Explore publications ↓</a><?php endif; ?>
		</div>
		<picture><source media="(max-width:800px)" srcset="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"><img src="<?php echo esc_url( amk_img( 'research-populations.webp' ) ); ?>" srcset="<?php echo esc_attr( amk_img( 'research-populations-600.webp' ) . ' 600w, ' . amk_img( 'research-populations.webp' ) . ' 1000w' ); ?>" sizes="(max-width:800px) calc(100vw - 40px), 600px" width="1000" height="1000" alt="Conceptual illustration of population health and connected communities"></picture>
	</div>
	<?php if ( $amk_scholar ) : ?>
	<div class="research-metrics">
		<div><strong><?php echo esc_html( count( $amk_pubs ) ); ?></strong><span>Publications, including preprints</span></div>
		<div><strong><?php echo esc_html( number_format( (int) ( $amk_scholar['citations'] ?? 0 ) ) ); ?></strong><span>Google Scholar citations</span></div>
		<div><strong><?php echo esc_html( $amk_scholar['h_index'] ?? '' ); ?></strong><span>h-index</span></div>
		<div><strong><?php echo esc_html( $amk_scholar['i10_index'] ?? '' ); ?></strong><span>i10-index</span></div>
	</div>
	<?php if ( ! empty( $amk_r['updated'] ) ) : ?><p class="metrics-note">Google Scholar · Updated <?php echo esc_html( wp_date( 'j F Y', strtotime( $amk_r['updated'] . ' 12:00:00' ) ) ); ?></p><?php endif; ?>
	<?php endif; ?>
	<?php amk_profile_links( $amk_p['links']['research'] ?? array() ); ?>
</div></section>

<?php if ( ! empty( $amk_p['projects'] ) ) : ?>
<section class="wrap section" id="projects">
	<div class="section-heading"><h2>Current projects</h2></div>
	<div class="project-list">
		<?php foreach ( $amk_p['projects'] as $amk_x ) : ?>
			<article><div><span class="project-status"><?php echo esc_html( $amk_x['status'] ?? '' ); ?></span><h3><?php echo esc_html( $amk_x['title'] ?? '' ); ?></h3></div><div><p><?php echo esc_html( $amk_x['role'] ?? '' ); ?></p><p><?php echo esc_html( $amk_x['with'] ?? '' ); ?></p></div></article>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<?php if ( ! empty( $amk_p['grants'] ) ) : ?>
<section class="funding-band" id="funding"><div class="wrap section">
	<div class="section-heading"><h2>Grants &amp; research funding</h2></div>
	<div class="funding-grid">
		<?php foreach ( $amk_p['grants'] as $amk_g ) : ?>
			<article><p class="meta"><?php echo esc_html( trim( ( $amk_g['year'] ?? '' ) . ' · ' . ( $amk_g['role'] ?? '' ), ' ·' ) ); ?></p><h3><?php echo esc_html( $amk_g['title'] ?? '' ); ?></h3><p><?php echo esc_html( $amk_g['funder'] ?? '' ); ?></p><strong><?php echo esc_html( $amk_g['amount'] ?? '' ); ?></strong></article>
		<?php endforeach; ?>
	</div>
</div></section>
<?php endif; ?>

<?php if ( $amk_pubs ) : ?>
<section class="wrap section" id="publications">
	<div class="section-heading"><h2>Articles, book chapters &amp; preprints</h2></div>
	<div class="archive-controls">
		<div class="field"><label for="publication-search">Search publications</label><input id="publication-search" type="search" placeholder="Title or journal…"></div>
		<div class="field"><label for="publication-year">Year</label><select id="publication-year"><option value="all">All years</option>
			<?php
			$amk_years = array_unique( array_filter( wp_list_pluck( $amk_pubs, 'year' ) ) );
			rsort( $amk_years );
			foreach ( $amk_years as $amk_y ) {
				echo '<option>' . esc_html( $amk_y ) . '</option>';
			}
			?>
		</select></div>
	</div>
	<p id="publication-status" class="result-status" role="status"><?php echo esc_html( count( $amk_pubs ) ); ?> publications</p>
	<div class="publication-library">
		<?php foreach ( $amk_pubs as $amk_x ) : $amk_type = $amk_x['type'] ?? ''; ?>
			<article data-year="<?php echo esc_attr( $amk_x['year'] ?? '' ); ?>" data-search="<?php echo esc_attr( amk_lower( ( $amk_x['title'] ?? '' ) . ' ' . ( $amk_x['venue'] ?? '' ) ) ); ?>"><span class="pub-year"><?php echo esc_html( ! empty( $amk_x['year'] ) ? $amk_x['year'] : '—' ); ?></span><div><h3><a href="<?php echo esc_url( $amk_x['link'] ?? '' ); ?>"><?php echo esc_html( $amk_x['title'] ?? '' ); ?></a></h3><p><?php echo esc_html( $amk_x['venue'] ?? '' ); ?> <span>· <?php echo esc_html( 'preprint' === $amk_type ? 'Preprint' : ( 'chapter' === $amk_type ? 'Book chapter' : 'Article' ) ); ?></span></p></div><a href="<?php echo esc_url( $amk_x['link'] ?? '' ); ?>" class="pub-open" aria-label="<?php echo esc_attr( 'Open publication: ' . ( $amk_x['title'] ?? '' ) ); ?>"><span aria-hidden="true">↗</span></a></article>
		<?php endforeach; ?>
	</div>
	<button type="button" class="button outline-dark" id="publication-more" hidden>Show all <?php echo esc_html( count( $amk_pubs ) ); ?> publications</button>
	<p id="publication-empty" class="empty-state" hidden>No publications match. Try another title, journal or year.</p>
</section>
<?php endif; ?>

<?php if ( ! empty( $amk_p['editorial'] ) || ! empty( $amk_p['reviewer'] ) ) : ?>
<section class="wrap section editorial-grid" id="reviewing">
	<?php if ( ! empty( $amk_p['editorial'] ) ) : ?><div><h2>Editorial roles</h2><ul><?php foreach ( $amk_p['editorial'] as $amk_t ) : ?><li><?php echo esc_html( $amk_t ); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
	<?php if ( ! empty( $amk_p['reviewer'] ) ) : ?><div><h2>Peer review</h2><p>Reviewer for <?php echo esc_html( count( $amk_p['reviewer'] ) ); ?> journals</p><details class="journal-list"><summary>View journals</summary><ul><?php foreach ( $amk_p['reviewer'] as $amk_t ) : ?><li><?php echo esc_html( $amk_t ); ?></li><?php endforeach; ?></ul></details></div><?php endif; ?>
</section>
<?php endif; ?>
<?php
amk_footer();
