<?php
/**
 * Template Name: Consultation
 *
 * Independent consultation services and how to ask for fee details by email.
 */
defined( 'ABSPATH' ) || exit;
amk_head();
amk_header( 'consultation' );
?>
<section class="consult-hero"><div class="wrap">
	<span class="eyebrow" style="color:#c62828">Independent consultation</span>
	<h1>Health research consultation</h1>
	<p>I offer independent support for researchers, clinicians, students and organisations at every stage of a health research project, from the first idea to a published paper. To get started or to ask about fees, email me with a short description of your work.</p>
	<a class="btn btn-consult" style="margin:24px 0 0" id="mail-top" href="mailto:abdullahdmc@gmail.com"><span class="pulse" aria-hidden="true"></span>Email abdullahdmc@gmail.com</a>
</div></section>

<section class="section" style="padding-top:24px"><div class="wrap">
	<div class="section-head"><div><span class="eyebrow">Services</span><h2>What I can help with</h2></div></div>
	<div class="services" id="services"></div>
</div></section>

<section class="section band"><div class="wrap">
	<div class="section-head"><div><span class="eyebrow">How it works</span><h2>From first email to delivery</h2></div></div>
	<ol class="steps">
		<li><b>Email me</b><span>Describe your project, the service you need and your timeline.</span></li>
		<li><b>Get the fee details</b><span>I reply with the scope of work, fee and expected delivery time.</span></li>
		<li><b>Agree and share</b><span>Once you confirm, share your protocol, data or manuscript.</span></li>
		<li><b>Delivery</b><span>You receive the work, with time for questions and revisions.</span></li>
	</ol>
</div></section>

<section class="section"><div class="wrap">
	<div class="email-card">
		<div>
			<h2>Contact for consultation and fee details</h2>
			<p>Write to</p>
			<div class="addr">abdullahdmc@gmail.com</div>
			<a class="btn" id="mail-bottom" href="mailto:abdullahdmc@gmail.com">Write an email →</a>
		</div>
		<div>
			<p style="margin:0 0 10px;font-weight:600;opacity:1">To get a quick and accurate quote, please include:</p>
			<ul>
				<li>The service you need</li>
				<li>Your study title, design and field</li>
				<li>For data analysis: sample size, number of variables and the software you prefer</li>
				<li>For manuscripts: word count and the target journal</li>
				<li>Your deadline</li>
			</ul>
		</div>
	</div>
</div></section>
<?php
amk_footer();
