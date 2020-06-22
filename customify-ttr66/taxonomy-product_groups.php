<?php 
/**
 * Template Name: Brand List by Group
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @package customify-ttr66
*/

get_header();
// TODO: добавить вывод контента страницы архива Brand (или можно использовать excerpt)
?>

<div class="content-inner">
    <div class="ttr66-brands-container">
        <?php
            // get Brands posts for the selected Product Group taxonomy term
            $args = array( 'post_type' => 'brand', 
                'nopaging' => true,
                'orderby' => 'menu_order', // (as suggested by Simple Page Ordering plugin)
                'order' => 'ASC'
            );

            $term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
            if($term) {
                $args['tax_query'] = array( 
                    array(
                    'taxonomy' => 'product_groups',
                    'field' => 'slug',
                    'terms' => $term->slug,
                    ) 
                );
            }
            $loop = new WP_Query( $args );

            // display posts if any
            if( $loop->have_posts() ) {
                while( $loop->have_posts() ): 
                    $loop->the_post(); ?>
                        <a href="<?php the_permalink(); ?>">
                            <div class="ttr66-brand-block">
                                <div class="ttr66-brand-logo">
                                    <!-- <a href="<?php echo $post->brand_url ?>"> -->
                                    <?php if( has_post_thumbnail() ) {
                                        the_post_thumbnail( 'thumbnail' );
                                    } else {
                                        $w = get_option( 'thumbnail' . '_size_w' );
                                        $h = get_option( 'thumbnail' . '_size_h' );
                                        echo '<svg width="'.$w.'" height="'.$h.'"> <rect width="'.$w.'" height="'.$h.'" style="fill:lightgray"/> </svg>';
                                    }?>
                                </div>
                                <div class="ttr66-brand-name"><?php the_title(); ?></div>
                            </div>
                        </a>            
                    <?php
                endwhile;
            } // if have_posts()
            wp_reset_postdata();
        ?>
    </div><!-- brands-container -->
</div><!-- content-inner -->

<?php 
// Sidebar settings -- see Layouts in theme options
get_footer();