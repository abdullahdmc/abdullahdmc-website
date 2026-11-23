<?php
/**
 * Template Name: Resources
 *
 * Links to the author's other websites and tools; the list comes from "resources" in the amk_profile option
 * (docs/assets/profile.js), so adding a resource needs no theme change.
 */
defined( 'ABSPATH' ) || exit;
$amk_items = amk_profile()['resources'] ?? array();
amk_head();
amk_header( 'resources' );
?>
<section class="wrap page-intro">
	<?php amk_crumb( 'Resources' ); ?>
	<h1>Resources</h1>
	<p class="lead">Websites and tools I have made for doctors, researchers and students.</p>
</section>
<section class="resource-band"><div class="wrap section">
	<?php if ( $amk_items ) : ?>
	<div class="resource-list">
		<?php foreach ( $amk_items as $amk_i ) : ?>
		<article class="resource-card">
			<?php if ( ! empty( $amk_i['label'] ) ) : ?><span class="label"><?php echo esc_html( $amk_i['label'] ); ?></span><?php endif; ?>
			<h2><a href="<?php echo esc_url( $amk_i['url'] ); ?>"><?php echo esc_html( $amk_i['title'] ); ?></a></h2>
			<?php if ( ! empty( $amk_i['desc'] ) ) : ?><p><?php echo esc_html( $amk_i['desc'] ); ?></p><?php endif; ?>
			<span class="resource-host"><?php echo esc_html( preg_replace( '#^https?://|/$#', '', $amk_i['url'] ) ); ?> <span aria-hidden="true">→</span></span>
		</article>
		<?php endforeach; ?>
	</div>
	<?php else : ?>
	<p class="lead">Resources will be listed here soon.</p>
	<?php endif; ?>
</div></section>
<?php
amk_footer();
