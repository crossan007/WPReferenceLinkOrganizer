<?php
/*
  Plugin Name: Reference Link Organizer
  Plugin URI: http://github.com/crossan007/WPReferenceLinkOrganizer
  Description: Provides a content type for cleanly storing links to various resources with searchable taxonomy and room for your own notes. Stop leaving 100's of tabs open, and save the link here instead
  Author: Charles Crossan
  Version: 0.0.1
  Author URI: http://crossan007.dev
*/


if (!class_exists('ReferenceLinkOrganizer')) {
  // WordPress class-based model Resources: https://carlalexander.ca/static-keyword-wordpress/
  // https://developer.wordpress.org/plugins/plugin-basics/best-practices/#object-oriented-programming-method

  class ReferenceLinkOrganizer {

    public static function get_plugin_base_name() {
      return 'reference_link';
    }

    private static function get_ui_labels() {
      return array(
        'name'                => _x( 'Reference Links', 'Post Type General Name', 'twentytwenty' ),
        'singular_name'       => _x( 'Reference Link', 'Post Type Singular Name', 'twentytwenty' ),
        'menu_name'           => __( 'Reference Links', 'twentytwenty' ),
        'all_items'           => __( 'All Reference Links', 'twentytwenty' ),
        'view_item'           => __( 'View Reference Link', 'twentytwenty' ),
        'add_new_item'        => __( 'Add New Reference Link', 'twentytwenty' ),
        'add_new'             => __( 'Add New', 'twentytwenty' ),
        'edit_item'           => __( 'Edit Reference Link', 'twentytwenty' ),
        'update_item'         => __( 'Update Reference Link', 'twentytwenty' ),
        'search_items'        => __( 'Search Reference Links', 'twentytwenty' ),
        'not_found'           => __( 'Not Found', 'twentytwenty' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'twentytwenty' ),
      );
    }

      // found this here: https://stackoverflow.com/a/61209067
      
    public static function meta_box_for_tools( $post ){
      add_meta_box(
        'my_meta_box_custom_id', 
        _( 'Additional info', 'textdomain' ), 
        array("ReferenceLinkOrganizer","render_meta_box_for_tools"), 
        ReferenceLinkOrganizer::get_plugin_base_name(), 
        'side', 
        'high' );
    }
      
    public static function render_meta_box_for_tools( $post ) {
      wp_nonce_field( basename( __FILE__ ), 'my_custom_meta_box_nonce' ); //used later for security
      echo '<p><input type="checkbox" name="is_this_featured" value="" '.get_post_meta($post->ID, 'tools_title', true).'/><label for="is_this_featured">'.__('Featured a Product?', 'textdomain').'</label></p>';

      echo '<p><input type="text" name="tool_url" value="" '.get_post_meta($post->ID, 'tools_title', true).'/><label for="is_this_featured">'.__('URL', 'textdomain').'</label></p>';

      echo '<p>'. 
        '<input type="radio" id="locatability_1" name="locatability" value="1" '.get_post_meta($post->ID, 'tools_title', true).'/>'.
        '<label for="locatability_1">'.__('Front Page of Google', 'textdomain').'</label><br/>'.
        '<input type="radio" id="locatability_2" name="locatability" value="2" '.get_post_meta($post->ID, 'tools_title', true).'/>'.
        '<label for="locatability_1">'.__('Second Page of Google', 'textdomain').'</label><br/>'.
        '<input type="radio" id="locatability_3" name="locatability" value="3" '.get_post_meta($post->ID, 'tools_title', true).'/>'.
        '<label for="locatability_1"><a target="_blank" href="https://xkcd.com/979/">'.__('Denver Coder 99s Home Address', 'textdomain').'</a></label>'.
        '</p>';


    }
    public static function tools_save_meta_boxes_data( $post_id ){
      // check for nonce to top xss
      if ( !isset( $_POST['my_custom_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['my_custom_meta_box_nonce'], basename( __FILE__ ) ) ){
          return;
      }
  
      // check for correct user capabilities - stop internal xss from customers
      if ( ! current_user_can( 'edit_post', $post_id ) ){
          return;
      }
  
      // update fields
      if ( isset( $_REQUEST['is_this_featured'] ) ) {
          update_post_meta( $post_id, 'is_this_featured', sanitize_text_field( $_POST['is_this_featured'] ) );
      }

      if ( isset( $_REQUEST['tool_url'] ) ) {
        update_post_meta( $post_id, 'tool_url', sanitize_text_field( $_POST['tool_url'] ) );
      }

      if ( isset( $_REQUEST['locatability'] ) ) {
        update_post_meta( $post_id, 'locatability', sanitize_text_field( $_POST['locatability'] ) );

      }

    }

    public static function get_post_type_args() {       
      // Set other options for Custom Post Type
       
      return array(
          'label'               => __( self::get_plugin_base_name(), 'twentytwenty' ),
          'description'         => __( 'Reference Links', 'twentytwenty' ),
          'labels'              => self::get_ui_labels(),
          // Features this CPT supports in Post Editor
          'supports'            => array( 'title', 'editor', 'author'),
          // You can associate this CPT with a taxonomy or custom taxonomy. 
          'taxonomies'          => array( 'post_tag','sm_project_attribute' ),
          /* A hierarchical CPT is like Pages and can have
          * Parent and child items. A non-hierarchical CPT
          * is like Posts.
          */ 
          'hierarchical'        => false,
          'public'              => true,
          'show_ui'             => true,
          'show_in_menu'        => true,
          'show_in_nav_menus'   => true,
          'show_in_admin_bar'   => true,
          'menu_position'       => 5,
          'can_export'          => true,
          'has_archive'         => true,
          'exclude_from_search' => false,
          'publicly_queryable'  => true,
          'capability_type'     => 'post',
          'show_in_rest' => true,
   
      );
             
    }
    public static function get_taxonomy_args() {
      // Add a taxonomy like tags
      $labels = array(
        'name'                       => 'Attributes',
        'singular_name'              => 'Attribute',
        'search_items'               => 'Attributes',
        'popular_items'              => 'Popular Attributes',
        'all_items'                  => 'All Attributes',
        'parent_item'                => null,
        'parent_item_colon'          => null,
        'edit_item'                  => 'Edit Attribute',
        'update_item'                => 'Update Attribute',
        'add_new_item'               => 'Add New Attribute',
        'new_item_name'              => 'New Attribute Name',
        'separate_items_with_commas' => 'Separate Attributes with commas',
        'add_or_remove_items'        => 'Add or remove Attributes',
        'choose_from_most_used'      => 'Choose from most used Attributes',
        'not_found'                  => 'No Attributes found',
        'menu_name'                  => 'Attributes',
      );

      return array(
        'hierarchical'          => false,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'show_in_rest'          => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'attribute' ),
      );

    }

      // found this here: https://wordpress.stackexchange.com/a/17388

      /* Filter the single_template with our custom function*/
      

    public static function my_custom_template($single) {

          global $post;

          /* Checks for single template by post type */
          if ( $post->post_type == ReferenceLinkOrganizer::get_plugin_base_name() ) {
              if ( file_exists( plugin_dir_path( __FILE__ ) . '/single-'.ReferenceLinkOrganizer::get_plugin_base_name().'.php' ) ) {
                  return plugin_dir_path( __FILE__ ) . '/single-'.ReferenceLinkOrganizer::get_plugin_base_name().'.php';
              }
          }

          return $single;

      }
  

    public static function init() {
      register_taxonomy('sm_project_attribute',self::get_plugin_base_name(),self::get_taxonomy_args());
      register_post_type( self::get_plugin_base_name(), self::get_post_type_args());
      add_action( 'add_meta_boxes_'.self::get_plugin_base_name(), array("ReferenceLinkOrganizer","meta_box_for_tools"));
      add_action( 'save_post_'.self::get_plugin_base_name(), array("ReferenceLinkOrganizer","tools_save_meta_boxes_data"), 10, 2 );
      add_filter('single_template', array("ReferenceLinkOrganizer","my_custom_template"));
    }

    public static function activate() {
      // One of the most common uses for an activation hook is to refresh WordPress permalinks 
      // when a plugin registers a custom post type. This gets rid of the nasty 404 errors.
      ReferenceLinkOrganizer::setup_post_type();
      flush_rewrite_rules(); 
    }

    public static function deactivate() {
      unregister_post_type( self::get_plugin_base_name() );
      flush_rewrite_rules();
    }
  }

  
  
  add_action( 'init', array('ReferenceLinkOrganizer','init'), 0 );
  register_activation_hook( __FILE__, array('ReferenceLinkOrganizer','activate'));
  register_deactivation_hook( __FILE__, array('ReferenceLinkOrganizer','deactivate') );
}

   
  


