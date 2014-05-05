<?php

/**
 *	Admin Tweaks
 *	Custom admin tweaks that allow you to create new user roles and permissions or just
 *  streamline the current interface for your non-savvy Wordpress users.
 */

require_once( 'admin-tweaks-functions.php' );

/**
 * Creating and Editing User Roles and Permissions
 */

// // On theme activation, do the following...
function admin_tweaks_activate_enable_roles($old_name, $old_theme = false) {
	
  // Role: Thinker (based on Contributor)
  add_role( 'dcdc_member', 'DCDC Member', array(
    // Administrator permissions:
		'create_users' => true,
		'delete_users' => true,
		'edit_users' => true,
		'list_users' => true,
		'remove_users' => true,
		'edit_dashboard' => true,
		'manage_options' => true,
		'edit_theme_options' => true,

		// Editor permissions:
		'moderate_comments' => true,
		'manage_categories' => true,
		'manage_links' => true,
		'edit_others_posts' => true,
		'edit_pages' => true,
		'edit_others_pages' => true,
		'edit_published_pages' => true,
		'publish_pages' => true,
		'delete_pages' => true,
		'delete_others_pages' => true,
		'delete_published_pages' => true,
		'delete_others_posts' => true,
		'delete_private_posts' => true,
		'edit_private_posts' => true,
		'read_private_posts' => true,
		'delete_private_pages' => true,
		'edit_private_pages' => true,
		'read_private_pages' => true,

		// Author permissions:
		'edit_published_posts' => true,
		'upload_files' => true,
		'publish_posts' => true,
		'delete_published_posts' => true,

		// Contributor permissions:
		'edit_posts' => true,
		'delete_posts' => true,

		// Subscriber permissions:
		'read' => true,
  ));

}
add_action( 'after_switch_theme', 'admin_tweaks_activate_enable_roles', 10, 2);

// On theme deactivation, restore all default roles.
function admin_tweaks_deactivate_disable_roles($newname, $newtheme) {
  remove_role( 'dcdc_member' );
}
add_action( 'switch_theme', 'admin_tweaks_deactivate_disable_roles', 10 , 2);


// Hide admin bar for users
// function low_level_user_hide_admin_bar() {
//   if ( ! current_user_can('edit_posts') ) {
//     add_filter('show_admin_bar', '__return_false'); 
//   }
// }
// add_action( 'after_setup_theme', 'low_level_user_hide_admin_bar' );

?>