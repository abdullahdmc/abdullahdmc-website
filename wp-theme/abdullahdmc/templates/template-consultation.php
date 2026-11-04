<?php
/**
 * Template Name: Consultation
 *
 * Independent consultation services and how to ask for fee details by email.
 */
defined( 'ABSPATH' ) || exit;
$amk_r     = amk_research();
$amk_email = amk_email();
$amk_mail  = 'mailto:' . $amk_email . '?subject=' . rawurlencode( 'Consultation request' );
$amk_services = array(
	'Proposal development'  => 'Research questions, study design, sample size, methods and analysis plans for grant applications, theses and ethics submissions.',
	'Data analysis'         => 'Statistical analysis in R, Stata, SPSS or Python for cross-sectional, case-control, cohort and trial data, systematic reviews and meta-analyses, and prediction models.',
	'Manuscript writing'    => 'Turning your results into a clear manuscript: structure, tables and figures, interpretation and reporting to journal guidelines.',
	'Manuscript review'     => 'A critical pre-submission review of methods, statistics, results and writing, with clear suggestions for improvement.',
	'Research consultation' => 'One-to-one advice on any stage of your project: methodology, analysis choices, responding to reviewers or planning your next study.',
);
$amk_steps = array(
	'Email me'            => 'Describe your project, the service you need and your timeline.',
	'Get the fee details' => 'I reply with the scope of work, fee and expected delivery time.',
	'Agree and share'     => 'Once you confirm, share your protocol, data or manuscript.',
	'Delivery'            => 'You receive the work, with time for questions and revisions.',
);
amk_head();
amk_header( 'consultation' );
?>
<section class="wrap page-intro">
	<?php amk_crumb( 'Consultation' ); ?>
	<div class="consultation-intro">
		<div>
			<h1>Health research<br><em>consultation</em></h1>
			<p class="lead">I offer independent support for researchers, clinicians, students and organisations at every stage of a health research project, from the first idea to a published paper. To get started or to ask about fees, email me with a short description of your work.</p>
			<a class="button gold" href="<?php echo esc_url( $amk_mail ); ?>">Email <?php echo esc_html( $amk_email ); ?> <span aria-hidden="true">→</span></a>
		</div>
		<img src="<?php echo esc_url( amk_img( 'consultation-workspace.webp' ) ); ?>" srcset="<?php echo esc_attr( amk_img( 'consultation-workspace-600.webp' ) . ' 600w, ' . amk_img( 'consultation-workspace.webp' ) . ' 1000w' ); ?>" sizes="(max-width:800px) calc(100vw - 40px), 600px" width="1000" height="1000" alt="Editorial illustration of researchers reviewing a manuscript together">
	</div>
	<?php if ( ! empty( $amk_r['publications'] ) ) : ?>
	<ul class="proof-line">
		<li><strong><?php echo esc_html( count( $amk_r['publications'] ) ); ?></strong> publications, including preprints</li>
		<?php if ( ! empty( $amk_r['scholar']['citations'] ) ) : ?><li><strong><?php echo esc_html( number_format( (int) $amk_r['scholar']['citations'] ) ); ?></strong> Google Scholar citations</li><?php endif; ?>
		<li>Data analysis for <strong>300+</strong> research projects since 2017</li>
		<li>Global Burden of Disease collaborator</li>
	</ul>
	<a class="text-link" href="<?php echo esc_url( amk_page_url( 'research' ) ); ?>">See the research record →</a>
	<?php endif; ?>
</section>
<section class="wrap section services-layout">
	<h2>What I can <br>help with</h2>
	<div class="service-list">
		<?php foreach ( $amk_services as $amk_t => $amk_d ) : ?>
			<article><h3><?php echo esc_html( $amk_t ); ?></h3><p><?php echo esc_html( $amk_d ); ?></p><a class="text-link" href="<?php echo esc_url( 'mailto:' . $amk_email . '?subject=' . rawurlencode( 'Consultation request — ' . $amk_t ) ); ?>">Enquire about <?php echo esc_html( strtolower( $amk_t ) ); ?> <span aria-hidden="true">→</span></a></article>
		<?php endforeach; ?>
	</div>
</section>
<section class="funding-band"><div class="wrap section">
	<h2>From first email to delivery</h2>
	<ol class="process-list"><?php foreach ( $amk_steps as $amk_t => $amk_d ) : ?><li><h3><?php echo esc_html( $amk_t ); ?></h3><p><?php echo esc_html( $amk_d ); ?></p></li><?php endforeach; ?></ol>
</div></section>
<section class="wrap section">
	<div class="consultation-contact">
		<div>
			<h2>Contact for consultation<br>and fee details</h2>
			<a class="contact-email" href="<?php echo esc_url( $amk_mail ); ?>"><?php echo esc_html( $amk_email ); ?></a>
			<button type="button" class="plain-button copy-email" data-copy="<?php echo esc_attr( $amk_email ); ?>">Copy email address</button><span class="sr-only" role="status" id="copy-email-status"></span>
			<a class="button gold" href="<?php echo esc_url( $amk_mail ); ?>">Write an email <span aria-hidden="true">→</span></a>
		</div>
		<div>
			<p>To get a quick and accurate quote, please include:</p>
			<ul><li>The service you need</li><li>Your study title, design and field</li><li>For data analysis: sample size, number of variables and the software you prefer</li><li>For manuscripts: word count and the target journal</li><li>Your deadline</li></ul>
		</div>
	</div>
</section>
<?php
amk_footer();
