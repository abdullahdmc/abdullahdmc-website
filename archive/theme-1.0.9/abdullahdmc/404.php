<?php
/**
 * Page not found.
 */
defined( 'ABSPATH' ) || exit;
amk_head();
amk_header();
?>
<section class="page-head"><div class="wrap">
	<span class="eyebrow">404</span>
	<h1>পাতাটি পাওয়া যায়নি · Page not found</h1>
	<p>The page may have moved. Try the writing list or search.</p>
	<p><a class="btn btn-primary" href="<?php echo esc_url( amk_writing_url() ); ?>">সব লেখা দেখুন →</a></p>
</div></section>
<?php
amk_footer();
