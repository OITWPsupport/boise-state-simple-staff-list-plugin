<?php

/**
* Plugin Name:	Boise State Simple Staff List
* Plugin URI:	https://github.com/OITWPsupport/boise-state-simple-staff-list-plugin
* Description:	A simple plugin to build and display a staff listing for your website, optimized for BSU.
* Version:		1.0.7
* Author:		Jen West
* Author URI:  http://www.boisestate.edu
* License:     GPL2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Domain Path: /lang
* Text Domain: my-toolset
**/

defined( 'ABSPATH' ) or die( 'Not to be used for the other use.' );

/*if( ! class_exists( 'Boise_State_SSL_Plugin_Updater' ) ){
	include_once( plugin_dir_path( __FILE__ ) . 'updater.php' );
}
$updater = new Boise_State_SSL_Plugin_Updater( __FILE__ );
$updater->set_username( 'OITWPsupport' );
$updater->set_repository( 'boise-state-simple-staff-list' );
$updater->initialize();*/
require_once( 'inc/BSUSSL_BFIGitHubPluginUpdater.php' );
if ( is_admin() ) {
    new BSUSSL_BFIGitHubPluginUpdater( __FILE__, 'OITWPsupport', "boise-state-simple-staff-list-plugin" );
}



/*
// Include some files and setup our plugin dir url
//////////////////////////////*/

define( 'STAFFLIST_PATH', plugin_dir_url(__FILE__) );
include_once('inc/admin-install-uninstall.php');
include_once('inc/admin-views.php');
include_once('inc/admin-save-data.php');
include_once('inc/admin-utilities.php');
include_once('inc/user-view-show-staff-list.php');
include_once('inc/updater.php');




/*
// Add post-thumbnails support for our custom post type
//////////////////////////////*/

add_theme_support( 'post-thumbnails', array( 'staff-member' ));





/*
// Register Activation/Deactivation Hooks
//////////////////////////////*/

// function location: /inc/admin-install-uninstall.php

register_activation_hook( __FILE__, 'boise_state_ssl_staff_member_activate' );
register_deactivation_hook( __FILE__, 'boise_state_ssl_staff_member_deactivate' );
register_uninstall_hook( __FILE__, 'boise_state_ssl_staff_member_uninstall' );


/*
// Enqueue Plugin Scripts and Styles
//////////////////////////////*/


function boise_state_ssl_staff_member_admin_print_scripts() {
	//** Admin Scripts
	wp_register_script( 'staff-member-admin-scripts', STAFFLIST_PATH . 'js/staff-member-admin-scripts.js', array('jquery', 'jquery-ui-sortable' ), '1.0', false );
	wp_enqueue_script( 'staff-member-admin-scripts' );
}

add_action( 'admin_enqueue_scripts', 'boise_state_ssl_staff_member_admin_enqueue_styles' );

function boise_state_ssl_staff_member_admin_enqueue_styles() {
	//** Admin Styles
	wp_register_style( 'staff-list-css', STAFFLIST_PATH . 'css/admin-staff-list.css' );
	wp_enqueue_style ( 'staff-list-css' );
}

add_action( 'wp_enqueue_scripts', 'boise_state_ssl_staff_member_public_enqueue_styles');

function boise_state_ssl_staff_member_public_enqueue_styles() {
	//** Front-end/Public facing Styles
	wp_register_style( 'staff-list-public-css', STAFFLIST_PATH . 'css/public-staff-list.css' );
	wp_enqueue_style ( 'staff-list-public-css' );
}

add_action( 'wp_enqueue_scripts', 'boise_state_ssl_staff_member_enqueue_styles');

function boise_state_ssl_staff_member_enqueue_styles(){
	//** Front-end Custom Style
	if (get_option('_staff_listing_write_external_css') == "yes") {
		wp_register_style( 'staff-list-custom-css', get_stylesheet_directory_uri() . '/simple-staff-list-custom.css' );
		wp_enqueue_style ( 'staff-list-custom-css' );
	}
}





/*
// Setup Our Staff Member CPT
//////////////////////////////*/

add_action( 'init', 'boise_state_ssl_staff_member_init' );

function boise_state_ssl_staff_member_init() {
    $labels = array(
        'name' => _x('Staff Members', 'post type general name'),
        'singular_name' => _x('Staff Member', 'post type singular name'),
        'add_new' => _x('Add New', 'staff member'),
        'add_new_item' => __('Add New Staff Member'),
        'edit_item' => __('Edit Staff Member'),
        'new_item' => __('New Staff Member'),
        'view_item' => __('View Staff Member'),
        'search_items' => __('Search Staff Members'),
        'exclude_from_search' => true,
        'not_found' =>  __('No staff members found'),
        'not_found_in_trash' => __('No staff members found in Trash'),
        'parent_item_colon' => '',
        'all_items' => 'All Staff Members',
        'menu_name' => 'Boise State Staff Members'
);

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'page',
        'has_archive' => false,
        'hierarchical' => false,
        'menu_position' => 100,
        'rewrite' => array('slug'=>'staff-members','with_front'=>false),
        'supports' => array( 'title', 'thumbnail', 'excerpt' )
    );

    register_post_type( 'staff-member', $args );
    
    
}





/*
// Setup Our Staff Group Taxonomy
//////////////////////////////*/

add_action( 'init', 'boise_state_ssl_custom_tax' );

function boise_state_ssl_custom_tax() {
	
	$labels = array(
		'name' => _x( 'Groups', 'taxonomy general name' ),
		'singular_name' => _x( 'Group', 'taxonomy singular name' ),
		'search_items' => __( 'Search Groups' ),
		'all_items' => __( 'All Groups' ),
		'parent_item' => __( 'Parent Group' ),
		'parent_item_colon' => __( 'Parent Group:' ),
		'edit_item' => __( 'Edit Group' ), 
		'update_item' => __( 'Update Group' ),
		'add_new_item' => __( 'Add New Group' ),
		'new_item_name' => __( 'New Group Name' ),
	); 	

	register_taxonomy( 'staff-member-group', array( 'staff-member' ), array(
		'hierarchical' => true,
		'labels' => $labels, /* NOTICE: Here is where the $labels variable is used */
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'group' ),
	));
}





/*
// Hide Excerpt Box by default
//////////////////////////////*/

// Change what's hidden by default
add_filter('default_hidden_meta_boxes', 'boise_state_ssl_hide_meta_lock', 10, 2);
function boise_state_ssl_hide_meta_lock($hidden, $screen) {
        if ( $screen->base == 'staff-member' )
                $hidden = array( 'postexcerpt' );
        return $hidden;
}





/*
// Change Title Text
//////////////////////////////*/

/**
 * Change "Enter Title Here" text
 * 
 * Changes title text on post edit screen for staff-member CPT
 *
 * @param    string    $screen    	get_current_screen()
 * @return   string               	returns new placeholder text for "Enter title here" input
 */
 
add_filter( 'enter_title_here', 'boise_state_ssl_staff_member_change_title' );
function boise_state_ssl_staff_member_change_title( $title ){
    $screen = get_current_screen();
    if ( $screen->post_type == 'staff-member' ) {
        $title = 'Staff Name';
    }
    return $title;
}





/*
// Add MetaBoxes
//////////////////////////////*/

/**
 * Change Featured Image title
 *
 * Removes the default featured image box and adds a new one with a new title
 * 
 */
 
add_action('do_meta_boxes', 'boise_state_ssl_staff_member_featured_image_text');
function boise_state_ssl_staff_member_featured_image_text() {

    remove_meta_box( 'postimagediv', 'staff-member', 'side' );
    if (current_theme_supports('post-thumbnails')) {
	    add_meta_box('postimagediv', __('Staff Photo'), 'post_thumbnail_meta_box', 'staff-member', 'normal', 'high');
	} else {
		add_meta_box('staff-member-warning', __('Staff Photo'), 'boise_state_ssl_staff_member_warning_meta_box', 'staff-member', 'normal', 'high');
	}
}


/**
 * Adds MetaBoxes for staff-member
 * 
 * All metabox callback functions are located in inc/admin-views.php
 *
 */

add_action('do_meta_boxes', 'boise_state_ssl_staff_member_add_meta_boxes');
function boise_state_ssl_staff_member_add_meta_boxes() {

    add_meta_box('staff-member-info', __('Staff Member Info'), 'boise_state_ssl_staff_member_info_meta_box', 'staff-member', 'normal', 'high');
    
    add_meta_box('staff-member-bio', __('Staff Member Bio'), 'boise_state_ssl_staff_member_bio_meta_box', 'staff-member', 'normal', 'high');
}





/*
// Create Custom Columns
//////////////////////////////*/


/**
 * Adds custom columns for staff-member CPT admin display
 *
 * @param    array    $cols    New column titles
 * @return   array             Column titles
 */
 
add_filter( "manage_staff-member_posts_columns", "boise_state_ssl_staff_member_custom_columns" );
function boise_state_ssl_staff_member_custom_columns( $cols ) {
	$cols = array(
		'cb'				  =>     '<input type="checkbox" />',
		'title'				  => __( 'Name' ),
		'photo'				  => __( 'Photo' ),
		'_staff_member_title' => __( 'Position' ),
		'_staff_member_email' => __( 'Email' ),
		'_staff_member_phone' => __( 'Phone' ),
		'_staff_member_bio'   => __( 'Bio' ),
	);
	return $cols;
}





/*
// Add SubPage for Ordering function
//////////////////////////////*/

/**
 * Registers sub pages for staff-member CPT
 * 
 * Adds "Order" and "Templates" page to WP nav. 
 * ALSO adds the print scripts action to load our js only on the pages we need it.
 *
 * @param    function    $order_page	    sets up the Order page
 * @param    function    $templates_page    sets up the Order page
 * 
 */
 
add_action( 'admin_menu', 'boise_state_ssl_staff_member_register_menu' );
function boise_state_ssl_staff_member_register_menu() {
	$order_page 	= add_submenu_page(
						'edit.php?post_type=staff-member',
						'Order Staff Members',
						'Order',
						'edit_pages', 'staff-member-order',
						'boise_state_ssl_staff_member_order_page'
					);
	
	$templates_page = add_submenu_page(
						'edit.php?post_type=staff-member',
						'Display Templates',
						'Templates',
						'edit_pages', 'staff-member-template',
						'boise_state_ssl_staff_member_template_page'
					);
	
	$usage_page 	= add_submenu_page(
						'edit.php?post_type=staff-member',
						'Simple Staff List Usage',
						'Usage',
						'edit_pages', 'staff-member-usage',
						'boise_state_ssl_staff_member_usage_page'
					);
	
	add_action( 'admin_print_scripts-'.$order_page, 'boise_state_ssl_staff_member_admin_print_scripts' );
	add_action( 'admin_print_scripts-'.$templates_page, 'boise_state_ssl_staff_member_admin_print_scripts' );
}





/*
// Make Sure We Add The Custom CSS File on Theme Switch
//////////////////////////////*/

function boise_state_ssl_staff_member_create_css_on_switch_theme($new_theme) {
    $filename = get_stylesheet_directory() . '/simple-staff-list-custom.css';
    $custom_css = get_option('_staff_listing_custom_css');
    file_put_contents($filename, $custom_css);
}
if ( get_option('_staff_listing_write_external_css') == 'yes' ){
	add_action('switch_theme', 'boise_state_ssl_staff_member_create_css_on_switch_theme');
}
?>
