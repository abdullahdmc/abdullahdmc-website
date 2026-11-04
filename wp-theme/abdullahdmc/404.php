<?php
/**
 * Page not found.
 */
defined( 'ABSPATH' ) || exit;
amk_head();
amk_header();
?>
<section class="wrap page-intro not-found">
	<?php echo amk_intro_art( 'door', 'A figure walking through a stone arch towards the light' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<h1>This page isn’t here.</h1>
	<p class="lead" lang="bn">পাতাটি পাওয়া যায়নি।</p>
	<p class="lead">It may have moved. Search the writing or go back to the homepage.</p>
	<form class="archive-controls" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<div class="field"><label for="notfound-search">Search the writing</label><input type="search" id="notfound-search" name="s" placeholder="বাংলা বা English…"></div>
		<button class="button outline-dark archive-submit" type="submit">Search</button>
	</form>
	<a class="button gold" href="<?php echo esc_url( home_url( '/' ) ); ?>">Home →</a> <a class="text-link" href="<?php echo esc_url( amk_writing_url() ); ?>">Browse writing →</a>
</section>
<?php
amk_footer();
