<?php
/**
 * Template Name: Biography
 *
 * Biography, affiliations, education, experience and training; filled from the amk_profile option.
 */
defined( 'ABSPATH' ) || exit;
amk_head();
amk_header( 'biography' );
?>
<section><div class="wrap bio-hero">
	<aside class="bio-side">
		<div class="portrait photo" style="background-image:url(<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/portrait.jpg' ); ?>)" role="img" aria-label="Dr. Md Abdullah Saeed Khan speaking at a conference"></div>
		<p class="contact">Contact: <a href="mailto:abdullahdmc@gmail.com">abdullahdmc@gmail.com</a></p>
		<h2 class="side-h">Profiles</h2>
		<div class="side-links" id="links"></div>
		<h2 class="side-h">YouTube</h2>
		<div class="side-links" id="yt-side"></div>
	</aside>
	<div>
		<span class="eyebrow">Biography</span>
		<h1 id="name">Dr. Md Abdullah Saeed Khan</h1>
		<p class="role">Assistant Professor (Epidemiology) · DPhil fellow, University of Oxford</p>
		<div class="bio-text" id="bio"></div>
	</div>
</div></section>

<section class="section"><div class="wrap">
	<span class="eyebrow">Affiliations</span><h2 class="sub-h" style="margin-top:10px">Current affiliations</h2>
	<div class="affil-grid" id="affil"></div>
</div></section>

<section class="section band"><div class="wrap two-col">
	<div><span class="eyebrow">Education</span><h2 class="sub-h" style="margin-top:10px">Academic qualifications</h2><ul class="edu" id="edu"></ul></div>
	<div>
		<span class="eyebrow">Recognition</span><h2 class="sub-h" style="margin-top:10px">Awards &amp; achievements</h2><ul class="ticks gold" id="awards"></ul>
		<h2 class="sub-h" style="margin-top:36px">Society memberships</h2><ul class="ticks" id="members"></ul>
	</div>
</div></section>

<section class="section"><div class="wrap">
	<span class="eyebrow">Experience</span><h2 class="sub-h" style="margin-top:10px">Experience</h2>
	<div class="tabs" role="tablist" id="xtabs"></div>
	<div id="xpanel"></div>
</div></section>

<section class="section band"><div class="wrap two-col">
	<div><span class="eyebrow">Training</span><h2 class="sub-h" style="margin-top:10px">Training &amp; courses</h2><ul class="rows" id="training"></ul></div>
	<div><span class="eyebrow">Conferences</span><h2 class="sub-h" style="margin-top:10px">Conferences &amp; presentations</h2><ul class="rows" id="conf"></ul></div>
</div></section>

<section class="section"><div class="wrap">
	<div class="cta">
		<div><h2>Get in touch</h2><p>For research collaboration, data analysis or speaking, write to <a href="mailto:abdullahdmc@gmail.com" style="color:#fff">abdullahdmc@gmail.com</a>.</p></div>
		<div class="links-row" id="social"></div>
	</div>
</div></section>
<?php
amk_footer();
