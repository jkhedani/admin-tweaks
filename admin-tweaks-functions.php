<?php

/**
 *  Admin Tweaks
 *  Use this theme add-on (soon to be plugin) to make modifications to admin interfaces.
 *  @todo how do we handle custom post type "editing" - creating and associating should be pretty easy
 *  @todo restrict permissions to view specific pages for particular roles...currently being "hidden" by css.
 *  @todo Probably best to move all admin/login/redirects to this document
 */


/**
 *  Enqueue custom stylesheet for admin styles
 */
function admin_style() {
    wp_enqueue_style( 'admin-style', get_stylesheet_directory_uri() . '/lib/admin-tweaks/admin-style.css' );
}
add_action('admin_enqueue_scripts', 'admin_style');
add_action('login_enqueue_scripts', 'admin_style');

/**
 *  Create unique body classes for various admin roles to modify layouts for
 *  different users. (e.g. hide the sidebar for non-admins, etc.)
 */
function admin_class_names($classes) {
  // If user is on the 'admin' side and is not an admin
  // Misleading function name: http://codex.wordpress.org/Function_Reference/is_admin
  if ( is_admin() && ( !current_user_can('promote_users') ) ) {
    // add 'class-name' to the $classes array
    $classes .= 'not-admin';
    // return the $classes array
    return $classes;
  } else {
    return $classes;
  }
}
add_filter('admin_body_class','admin_class_names');

/**
 * Creating and Editing User Roles and Permissions
 */

// On theme activation, do the following...
function course_theme_activate_enable_roles($old_name, $old_theme = false) {
  remove_role("editor"); // Role: Editor (default)
  remove_role("author"); // Role: Author (default)
  remove_role("contributor"); // Role: Contributor (default)
  remove_role("subscriber"); // Role: Subscriber (default)
}
add_action("after_switch_theme", "course_theme_activate_enable_roles", 10, 2);

// On theme deactivation, restore all default roles.
function course_theme_deactivate_disable_roles($newname, $newtheme) {
  
  // Role: Editor (default)
  add_role('editor', 'Editor', array(
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

  // Role: Author (default)
  add_role('author', 'Author', array(
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

  // Role: Contributor (default)
  add_role('contributor', 'Contributor', array(
    // Contributor permissions:
    'edit_posts' => true,
    'delete_posts' => true,

    // Subscriber permissions:
    'read' => true,
  ));

  // Role: Subscriber (default)
  add_role('subscriber', 'Subscriber', array(
    // Subscriber permissions:
    'read' => true,
  ));
}
add_action("switch_theme", "course_theme_deactivate_disable_roles", 10 , 2);


/**
 *  User Herding
 */

// Redirect users who can't edit posts away from any admin section
function low_level_user_redirect_admin() {
  if ( ! current_user_can('edit_posts') ) {
    wp_redirect( site_url() );
    exit;
  }
}
add_action( 'admin_init', 'low_level_user_redirect_admin' );

/**
 *  Toolbar Tweaks
 *  http://codex.wordpress.org/Class_Reference/WP_Admin_Bar
 */
function toolbar_tweaks() {
  global $wp_admin_bar;

  // Remove these menu items (for now)
  $wp_admin_bar->remove_node( 'search' );
  $wp_admin_bar->remove_node( 'dashboard' );
  $wp_admin_bar->remove_node( 'site-name' ); // Re-creating on our own for more control.
  $wp_admin_bar->remove_node( 'wp-logo' );
  $wp_admin_bar->remove_node( 'comments' );
  $wp_admin_bar->remove_node( 'new-post' ); // Subnode removed from "+ New"
  $wp_admin_bar->remove_node( 'new-media' ); // Subnode removed from "+ New"

  // Hide "My Sites" from users associated with only one site on a multisite build.
  if ( is_multisite() ) {
    $current_user = wp_get_current_user();
    if ( count( get_blogs_of_user( $current_user->ID ) ) == 1 ) {
      $wp_admin_bar->remove_node( 'my-sites' );   
    }
  }
}
add_action( 'wp_before_admin_bar_render', 'toolbar_tweaks' );

/**
 *  Additional Toolbar Links
 */
function add_useful_toolbar_menu() {

  global $wp_admin_bar;

  // Modify the toolbar for individuals who
  if ( current_user_can('edit_posts') ) {

    // Create ability for admins to jump back and forth from the admin screens
    // to the front-facing screens using the convenient link in the top left
    // of the admin bar. But, disable such ability for non-admin users.
    if ( !current_user_can('promote_users') ) {
      $location = get_home_url();
    } else {
      if ( is_admin() ) {
        $location = get_home_url();
      } else {
        $location = get_admin_url();
      }
    }

    // A. Site Name
    $wp_admin_bar->add_node( array(
      'id' => 'back-to-home',
      'title' => get_bloginfo('name'),
      'meta' => array(),
      'href' => $location,
    ));

    // B. View All Content Types
    $wp_admin_bar->add_node( array(
      'id' => 'view-all',
      'title' => 'View All Content',
      'meta' => array(),
      'href' => $location,
    ));
    $postTypes = get_post_types( array( '_builtin' => false ), 'object' );
    foreach ($postTypes as $postType) {
      if ( $postType->name === 'acf' ) {
        // Do nothing. Do not create a menu item for ACF field group content type.
      } else {
        // C. View All Custom Post Type Menu
        $wp_admin_bar->add_node( array(
          'parent' => 'view-all',
          'id' => 'view-all-'.$postType->label,
          'meta' => array(),
          'title' => $postType->label,
          'href' => get_admin_url() . 'edit.php?post_type="' .$postType->name. '"',
        )); 

        // D. View Different Site Listing
        $wp_admin_bar->add_node( array(
          'parent' => 'back-to-home',
          'id' => 'back-to-home-'.$postType->label,
          'meta' => array(),
          'title' => $postType->label,
          'href' => get_site_url() . '/' . $postType->name,
        )); 

      }
    }

    // Modify "Howdy in Menu Bar"
    $user_id      = get_current_user_id();
    $current_user = wp_get_current_user();
    $my_url       = 'http://www.google.com';

    if ( ! $user_id )
        return;

    $avatar = get_avatar( $user_id, 16 );
    $howdy  = sprintf( __('Aloha e %1$s'), $current_user->display_name );
    $class  = empty( $avatar ) ? '' : 'with-avatar';

    $wp_admin_bar->add_menu( array(
        'id'        => 'my-account',
        'parent'    => 'top-secondary',
        'title'     => $howdy . $avatar,
        'href'      => $my_url,
        'meta'      => array(
            'class'     => $class,
            'title'     => __('My Account'),
        ),
    ) );

  } // current_user_can edit_posts
}
add_action( 'admin_bar_menu', 'add_useful_toolbar_menu', 25 );


/**
 *  Login Redirects
 */

// Redirect logged all users except admins to the home page instead of the profiles page and/or dashboard page
// More info: http://codex.wordpress.org/Plugin_API/Filter_Reference/login_redirect
function admin_tweaks_login_redirect( $redirect_to, $request, $user ){
  //is there a user to check?
  global $user;
  if( isset( $user->roles ) && is_array( $user->roles ) ) {
    //check for admins
    if( in_array( "administrator", $user->roles ) ) {
      return $redirect_to; // redirect them to the default place
    } else {
      return home_url();
    }
  } else {
    return $redirect_to;
  }
}
add_filter( 'login_redirect', 'admin_tweaks_login_redirect', 10, 3 );

/**
 * Show On Screen Defaults
 */

// function change_default_hidden( $hidden, $screen ) {
//   if ( 'modules' == $screen->id ) {
//     $hidden = array_flip($hidden);
//     unset($hidden['authordiv']); //show author box
//     $hidden = array_flip($hidden);
//     $hidden[] = 'commentstatusdiv'; //hide page attributes
//   }
//   return $hidden;
// }
// add_filter( 'default_hidden_meta_boxes', 'change_default_hidden', 10, 2 );

/**
 *  Admin Styles
 */

// function admin_style() {
//     wp_enqueue_style( 'admin-style', get_stylesheet_directory_uri() . '/lib/admin-tweaks/admin-style.css' );
// }
// add_action('admin_enqueue_scripts', 'admin_style');
// add_action('login_enqueue_scripts', 'admin_style');

// function admin_class_names($classes) {
//  // If user is on the 'admin' side and is not an admin
//  // Misleading function name: http://codex.wordpress.org/Function_Reference/is_admin
//  if( is_admin() && (!current_user_can('manage_options') || get_current_user_role() == 'instructional_designer')) {
//    // add 'class-name' to the $classes array
//    $classes .= 'not-admin';
//    // return the $classes array
//    return $classes;
//  } else {
//    return $classes;
//  }
// }
// add_filter('admin_body_class','admin_class_names');

// function add_useful_toolbar_menu() {
//  global $wp_admin_bar;
//  if ( current_user_can('edit_posts') ) {
//    // Course Name Menu
//    $wp_admin_bar->add_menu( array(
//      'id' => 'course-name',
//      'title' => get_bloginfo( 'course-name' ),
//      'meta' => array(),
//      'href' => get_home_url(),
//    ));
//    // Course Options
//    $wp_admin_bar->add_menu( array(
//      'parent' => 'course-name',
//      'id' => 'course-name-course-options',
//      'meta' => array(),
//      'title' => 'Course Options',
//      'href' => get_admin_url() . 'customize.php',
//    ));
//    // Table of Contents
//    $wp_admin_bar->add_menu( array(
//      'parent' => 'course-name',
//      'id' => 'course-name-toc',
//      'meta' => array(),
//      'title' => 'Table of Contents',
//      'href' => get_admin_url() . 'admin.php?page=global-sort.php',
//    ));
//  }
// }
// add_action( 'admin_bar_menu', 'add_useful_toolbar_menu', 25 );


?>