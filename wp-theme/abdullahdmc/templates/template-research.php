<?php
/**
 * Template Name: Research
 *
 * Metrics, projects, funding, publications and reviewing; filled from the amk_research and amk_profile options.
 */
defined( 'ABSPATH' ) || exit;
amk_head();
amk_header( 'research' );
?>
<section class="page-head"><div class="wrap">
	<span class="eyebrow">Research</span>
	<h1>Infectious disease, outcomes &amp; health data science</h1>
	<p>Clinical epidemiology of tetanus, dengue, COVID-19 and febrile illness; mental health and quality of life; health systems; and machine-learning prediction models. Global Burden of Disease collaborator.</p>
	<div class="links-row" id="rlinks" style="margin-top:18px"></div>
</div></section>

<section><div class="wrap">
	<div class="metrics" id="metrics"></div>
	<div class="metrics-note" id="mnote"></div>
</div></section>

<section class="section" id="projects"><div class="wrap">
	<div class="section-head"><div><span class="eyebrow">Now</span><h2>Current projects</h2></div></div>
	<div class="cards2" id="proj"></div>
</div></section>

<section class="section band" id="funding"><div class="wrap">
	<div class="section-head"><div><span class="eyebrow">Funding</span><h2>Grants &amp; research funding</h2></div></div>
	<div class="cards2" id="grants"></div>
</div></section>

<section class="section" id="publications"><div class="wrap">
	<div class="section-head"><div><span class="eyebrow">Publications</span><h2>Peer-reviewed publications</h2></div></div>
	<div class="pub-tabs">
		<div class="chips" id="ptabs"></div>
		<label class="search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
			<input id="pq" type="search" placeholder="Search titles or journals…" aria-label="Search publications"></label>
	</div>
	<div id="plist"></div>
</div></section>

<section class="section band" id="reviewing"><div class="wrap">
	<div><span class="eyebrow">Editorial</span><h2 class="sub-h" style="margin-top:10px">Editorial roles</h2><ul class="ticks gold" id="editorial"></ul></div>
	<div style="margin-top:44px"><span class="eyebrow">Peer review</span><h2 class="sub-h" style="margin-top:10px">Reviewer for <span id="rcount"></span> journals</h2><div class="chips-wrap" id="reviewer"></div></div>
</div></section>
<?php
amk_footer();
