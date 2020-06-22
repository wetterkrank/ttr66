<?php
/**
 * Template Name: Single Brand
 * Template for displaying single Brand posts
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 * @package customify-ttr66
 */

get_header(); ?>
	<div class="content-inner">
		<?php
			while ( have_posts() ) :
			the_post();
			?>
				<div class="ttr66-single-brand-container">
					<div class="ttr66-single-brand-card">
						<div class="ttr66-single-brand-logo">
										<a href="<?php echo $post->brand_url ?>">
											<?php if( has_post_thumbnail() ) {
												the_post_thumbnail( 'medium' );
											} else {
												$w = get_option( 'thumbnail' . '_size_w' );
												$h = get_option( 'thumbnail' . '_size_h' );
												echo '<svg width="'.$w.'" height="'.$h.'"> <rect width="'.$w.'" height="'.$h.'" style="fill:lightgray"/> </svg>';
											}?>
										</a>
						</div>
						<div class="ttr66-single-brand-desc">
							<!-- <ul> -->
								<p><a href="<?php echo $post->brand_url; ?>"><?php echo $post->brand_url; ?></a></p>
								<?php if ($post->brand_desc) echo $post->brand_desc; ?>
							<!-- </ul> -->
						</div>
					</div>
					<div class="ttr66-single-brand-pricelist"><?php the_content(); ?></div>
				</div><!-- single-brand -->
			<?php
			endwhile; // End of the loop.
		?>
	</div><!-- #.content-inner -->
<?php
// Sidebar settings -- see Layouts in theme options
get_footer();