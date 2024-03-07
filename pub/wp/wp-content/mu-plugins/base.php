<?php
/**
 * Plugin Name: Base
 * Plugin URI: https://www.selesti.com
 * Description: Adds custom functionality for Signwaves
 * Version: 0.0.1
 * Author: Selesti Ltd
 * Author URI: https://www.selesti.com/
 * License: Private
 */

add_action('acf/init', function () {
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => 'Bartender Hub',
            'icon_url' => 'dashicons-store',
        ]);
    }
});

add_action('admin_menu', function () {
    remove_menu_page('jetpack');                    //Jetpack*
    remove_menu_page('upload.php');                 // Media
    remove_menu_page('edit.php?post_type=page');    //Pages
    remove_menu_page('edit-comments.php');          //Comments
    remove_submenu_page('themes.php', 'customize.php');
});

/**
 * Saves post type and taxonomy data to JSON files in the theme directory.
 *
 * @param array $data Array of post type data that was just saved.
 */
function pluginize_local_cptui_data($data = [])
{
    $theme_dir = get_stylesheet_directory();
    // Create our directory if it doesn't exist.
    if (!is_dir($theme_dir .= '/cptui_data')) {
        mkdir($theme_dir, 0755);
    }

    if (array_key_exists('cpt_custom_post_type', $data)) {
        // Fetch all of our post types and encode into JSON.
        $cptui_post_types = get_option('cptui_post_types', []);
        $content = json_encode($cptui_post_types);
        // Save the encoded JSON to a primary file holding all of them.
        file_put_contents($theme_dir . '/cptui_post_type_data.json', $content);
    }

    if (array_key_exists('cpt_custom_tax', $data)) {
        // Fetch all of our taxonomies and encode into JSON.
        $cptui_taxonomies = get_option('cptui_taxonomies', []);
        $content = json_encode($cptui_taxonomies);
        // Save the encoded JSON to a primary file holding all of them.
        file_put_contents($theme_dir . '/cptui_taxonomy_data.json', $content);
    }
}

add_action('cptui_after_update_post_type', 'pluginize_local_cptui_data');
add_action('cptui_after_update_taxonomy', 'pluginize_local_cptui_data');


function remove_dashboard_meta()
{
    remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
    remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
    remove_meta_box('dashboard_primary', 'dashboard', 'side');
    remove_meta_box('dashboard_secondary', 'dashboard', 'normal');
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
    remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
}

add_action('admin_init', 'remove_dashboard_meta');


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
