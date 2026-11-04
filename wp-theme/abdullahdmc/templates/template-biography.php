<?php
/**
 * Template Name: Biography
 *
 * Biography, affiliations, education, experience and training; filled from the amk_profile option.
 */
defined( 'ABSPATH' ) || exit;
$amk_p     = amk_profile();
$amk_bio   = (array) ( $amk_p['bio'] ?? array() );
$amk_email = amk_email();
$amk_x     = (array) ( $amk_p['experience'] ?? array() );
$amk_aff   = (array) ( $amk_p['affiliations'] ?? array() );
usort( $amk_aff, function ( $a, $b ) {
	return strcmp( (string) ( $b['since'] ?? '' ), (string) ( $a['since'] ?? '' ) );
} );
amk_head();
amk_header( 'biography' );
?>
<section class="wrap page-intro">
	<?php amk_crumb( 'Biography' ); ?>
	<div class="biography-intro">
		<div>
			<h1>Dr. Md Abdullah<br>Saeed Khan</h1>
			<p class="lead">Assistant Professor (Epidemiology) · DPhil fellow, University of Oxford</p>
			<div class="bio-opening"><?php foreach ( array_slice( $amk_bio, 0, 1 ) as $amk_s ) : ?><p><?php echo esc_html( $amk_s ); ?></p><?php endforeach; ?></div>
			<a class="text-link" href="mailto:<?php echo esc_attr( $amk_email ); ?>">Get in touch <span aria-hidden="true">→</span></a>
		</div>
		<figure class="bio-portrait"><img src="<?php echo esc_url( amk_img( 'portrait-600.webp' ) ); ?>" alt="Dr. Md Abdullah Saeed Khan speaking at a conference" width="600" height="700"><figcaption>Physician · Epidemiologist · Researcher</figcaption></figure>
	</div>
</section>
<div class="wrap biography-layout">
	<aside class="bio-nav"><nav aria-label="Biography sections"><a href="#about">About</a><a href="#affiliations">Affiliations</a><a href="#education">Education</a><a href="#experience">Experience</a><a href="#training">Training</a><a href="#recognition">Recognition</a></nav></aside>
	<div>
		<section class="bio-section" id="about">
			<?php foreach ( array_slice( $amk_bio, 1 ) as $amk_s ) : ?><p><?php echo esc_html( $amk_s ); ?></p><?php endforeach; ?>
			<?php amk_profile_links( array_merge( $amk_p['links']['research'] ?? array(), $amk_p['links']['social'] ?? array() ) ); ?>
		</section>
		<section class="bio-section" id="affiliations"><h2>Current affiliations</h2><?php amk_timeline( array_map( function ( $a ) { return array( $a['org'] ?? '', $a['role'] ?? '', $a['since'] ?? '' ); }, $amk_aff ) ); ?></section>
		<section class="bio-section" id="education"><h2>Academic qualifications</h2><?php amk_timeline( array_map( function ( $a ) { return array( $a['degree'] ?? '', $a['inst'] ?? '', $a['year'] ?? '', $a['note'] ?? '' ); }, (array) ( $amk_p['education'] ?? array() ) ) ); ?></section>
		<section class="bio-section" id="experience"><h2>Experience</h2>
			<?php
			$amk_groups = array( 'professional' => 'Professional appointments', 'research' => 'Research', 'analysis' => 'Health data analysis', 'teaching' => 'Teaching' );
			$amk_first  = true;
			foreach ( $amk_groups as $amk_key => $amk_label ) :
				if ( empty( $amk_x[ $amk_key ] ) ) {
					continue;
				}
				?>
				<details class="experience-group"<?php echo $amk_first ? ' open' : ''; ?>><summary><?php echo esc_html( $amk_label ); ?></summary><?php amk_timeline( $amk_x[ $amk_key ] ); ?></details>
				<?php
				$amk_first = false;
			endforeach;
			?>
			<?php if ( ! empty( $amk_x['tools'] ) ) : ?><p class="tools-line"><?php echo esc_html( implode( ' · ', $amk_x['tools'] ) ); ?></p><?php endif; ?>
		</section>
		<section class="bio-section" id="training"><h2>Training &amp; courses</h2><?php amk_timeline( (array) ( $amk_p['training'] ?? array() ) ); ?><h2>Conferences &amp; presentations</h2><?php amk_timeline( (array) ( $amk_p['conferences'] ?? array() ) ); ?></section>
		<section class="bio-section" id="recognition">
			<h2>Awards &amp; achievements</h2><ul class="recognition-list"><?php foreach ( (array) ( $amk_p['awards'] ?? array() ) as $amk_s ) : ?><li><?php echo esc_html( $amk_s ); ?></li><?php endforeach; ?></ul>
			<h2>Society memberships</h2><ul class="recognition-list"><?php foreach ( (array) ( $amk_p['memberships'] ?? array() ) as $amk_s ) : ?><li><?php echo esc_html( $amk_s ); ?></li><?php endforeach; ?></ul>
		</section>
	</div>
</div>
<section class="wrap section"><div class="contact-band"><h2>Get in touch</h2><p>For research collaboration, data analysis or speaking, write to <a href="mailto:<?php echo esc_attr( $amk_email ); ?>"><?php echo esc_html( $amk_email ); ?></a>.</p></div></section>
<?php
amk_footer();
