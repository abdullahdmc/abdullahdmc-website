<?php
/**
 * A single post: cover, title, byline, text, categories and related posts.
 */
defined( 'ABSPATH' ) || exit;
amk_head();
amk_header( 'writing' );
while ( have_posts() ) :
	the_post();
	$amk_key   = amk_theme_of();
	$amk_theme = amk_themes()[ $amk_key ];
	$amk_en    = ! amk_is_bangla( get_the_title() );
	?>
	<article <?php post_class(); ?>>
		<div class="wrap post-hero"><?php echo amk_cover( null, 'amk-cover', 'lg' ); // phpcs:ignore ?></div>
		<div class="wrap">
			<header class="post-head">
				<div class="card-meta" style="--c1:<?php echo esc_attr( $amk_theme['c1'] ); ?>"><span class="dot"></span>
					<a href="<?php echo esc_url( amk_writing_url( array( 'theme' => $amk_key ) ) ); ?>"><?php echo esc_html( $amk_theme['bn'] ); ?></a> · <?php echo esc_html( amk_date() ); ?></div>
				<h1><?php the_title(); ?></h1>
				<div class="byline"><span class="brand-mark">AK</span><div><b>Dr. Md Abdullah Saeed Khan</b><span>Assistant Professor (Epidemiology)</span></div></div>
			</header>
			<div class="prose<?php echo $amk_en ? ' en' : ''; ?>"><?php the_content(); ?></div>
			<div class="post-foot">
				<?php foreach ( get_the_category() as $amk_cat ) : ?>
					<a class="tag" href="<?php echo esc_url( get_category_link( $amk_cat ) ); ?>"><?php echo esc_html( $amk_cat->name ); ?></a>
				<?php endforeach; ?>
				<?php foreach ( (array) get_the_tags() as $amk_tag ) : ?>
					<?php if ( $amk_tag ) : ?><a class="tag" href="<?php echo esc_url( get_tag_link( $amk_tag ) ); ?>">#<?php echo esc_html( $amk_tag->name ); ?></a><?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</article>
	<?php
	$amk_related = new WP_Query( array(
		'category__in'        => amk_theme_category_ids( $amk_key ),
		'post__not_in'        => array( get_the_ID() ),
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
	) );
	if ( $amk_related->have_posts() ) :
		?>
		<section class="section"><div class="wrap">
			<div class="section-head"><div><span class="eyebrow">আরও পড়ুন</span><h2>একই বিষয়ে আরও লেখা</h2></div></div>
			<div class="grid"><?php while ( $amk_related->have_posts() ) { $amk_related->the_post(); amk_card(); } wp_reset_postdata(); ?></div>
		</div></section>
		<?php
	endif;
endwhile;
amk_footer();
