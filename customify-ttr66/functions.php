<?php

// Enqueue parent and own styles for our theme
function ttr66_enqueue_styles() {
    $parent_style = 'customify';
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get( 'Version' )
    );
}
add_action( 'wp_enqueue_scripts', 'ttr66_enqueue_styles' );

// Enqueue styles and scripts for Datatables @ single Brand page
function ttr66_page_scripts() {
    if ( is_singular( 'brand' ) ) {
        wp_enqueue_style( 'ttr66-datatables-css', get_stylesheet_directory_uri() . '/datatables/datatables.min.css', array(), '1.0.0', 'all' );
        wp_enqueue_script( 'ttr66-datatables-js', get_stylesheet_directory_uri() . '/datatables/datatables.min.js', array( 'jquery' ), '1.0.0', false );
        wp_enqueue_script( 'ttr66-datatables-init-js', get_stylesheet_directory_uri() . '/datatables/datatables-init.js', array( 'jquery' ), '1.0.0', false );
    }
}
add_action('wp_enqueue_scripts', 'ttr66_page_scripts');

// Total comments annihilation
add_action('admin_init', function () {
    // Redirect any user trying to access comments page
    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
    }

    // Remove comments metabox from dashboard
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

    // Disable support for comments and trackbacks in post types
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

// Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// Remove comments page in menu
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

// Remove comments links from admin bar
add_action('init', function () {
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
});
