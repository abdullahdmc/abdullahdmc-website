<?php
/**
 * Category, tag, author and date archives. The "Arguments for God" series tag lists its parts in order.
 */
defined( 'ABSPATH' ) || exit;
$amk_series = is_tag( AMK_SERIES_TAG );
$amk_title  = $amk_series ? 'স্রষ্টার অস্তিত্বের যুক্তি' : wp_strip_all_tags( single_term_title( '', false ) ? single_term_title( '', false ) : get_the_archive_title() );
amk_head();
amk_header( 'writing' );
?>
<section class="wrap page-intro">
	<?php amk_crumb( $amk_title, array( 'Writing' => amk_writing_url() ) ); ?>
	<div class="intro-line"><div>
		<h1<?php echo amk_is_bangla( $amk_title ) ? ' lang="bn"' : ''; ?>><?php echo esc_html( $amk_title ); ?></h1>
		<?php if ( $amk_series ) : ?>
			<p class="lead" lang="bn">অ্যারিস্টটল, আকুইনাস, ইবনে সিনা থেকে লাইবনিজ: দর্শনের ইতিহাসের প্রধান যুক্তিগুলো। <?php echo esc_html( amk_bn_digits( count( amk_series_ids() ) ) ); ?> পর্ব, প্রথম পর্ব থেকে সাজানো।</p>
		<?php else : ?>
			<?php the_archive_description( '<div class="lead">', '</div>' ); ?>
		<?php endif; ?>
	</div><?php echo $amk_series ? amk_intro_art( 'door', 'A figure walking through a stone arch towards the light' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
</section>
<section class="wrap archive-section" id="archive">
	<div class="archive-result-line"><p><?php echo esc_html( sprintf( 1 === (int) $wp_query->found_posts ? '%s entry' : '%s entries', number_format( (int) $wp_query->found_posts ) ) ); ?></p><a class="plain-button" href="<?php echo esc_url( amk_writing_url() ); ?>">All writing</a></div>
	<?php amk_grid(); ?>
</section>
<?php
amk_footer();
