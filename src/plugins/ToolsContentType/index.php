<?php
/*
  Plugin Name: ToolsWPContentType
  Plugin URI: http://github.com/crossan007/ToolsWPContentType
  Description: ToolsWPContentType
  Author: Charles Crossan
  Version: 0.0.01
  Author URI: http://crossan007.dev
*/


/*
* Creating a function to create our CPT
*/
 
function custom_post_type() {

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

  $args = array(
    'hierarchical'          => false,
    'labels'                => $labels,
    'show_ui'               => true,
    'show_admin_column'     => true,
    'show_in_rest'          => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var'             => true,
    'rewrite'               => array( 'slug' => 'attribute' ),
  );

  register_taxonomy('sm_project_attribute','tools',$args);

  
 
  // Set UI labels for Custom Post Type
      $labels = array(
          'name'                => _x( 'Tools', 'Post Type General Name', 'twentytwenty' ),
          'singular_name'       => _x( 'Tool', 'Post Type Singular Name', 'twentytwenty' ),
          'menu_name'           => __( 'Tools', 'twentytwenty' ),
          'parent_item_colon'   => __( 'Parent Tool', 'twentytwenty' ),
          'all_items'           => __( 'All Tools', 'twentytwenty' ),
          'view_item'           => __( 'View Tool', 'twentytwenty' ),
          'add_new_item'        => __( 'Add New Tool', 'twentytwenty' ),
          'add_new'             => __( 'Add New', 'twentytwenty' ),
          'edit_item'           => __( 'Edit Tool', 'twentytwenty' ),
          'update_item'         => __( 'Update Tool', 'twentytwenty' ),
          'search_items'        => __( 'Search Tool', 'twentytwenty' ),
          'not_found'           => __( 'Not Found', 'twentytwenty' ),
          'not_found_in_trash'  => __( 'Not found in Trash', 'twentytwenty' ),
      );
       
  // Set other options for Custom Post Type
       
      $args = array(
          'label'               => __( 'tools', 'twentytwenty' ),
          'description'         => __( 'Tool reviews', 'twentytwenty' ),
          'labels'              => $labels,
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
       
      // Registering your Custom Post Type
      register_post_type( 'tools', $args );

       


    // found this here: https://stackoverflow.com/a/61209067
    add_action( 'add_meta_boxes_tools', 'meta_box_for_tools' );
    function meta_box_for_tools( $post ){
        add_meta_box( 'my_meta_box_custom_id', __( 'Additional info', 'textdomain' ), 'my_custom_meta_box_html_output', 'tools', 'normal', 'core' );
    }
    
    function my_custom_meta_box_html_output( $post ) {
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
    
    add_action( 'save_post_tools', 'tools_save_meta_boxes_data', 10, 2 );
    function tools_save_meta_boxes_data( $post_id ){
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

    // found this here: https://wordpress.stackexchange.com/a/17388

    /* Filter the single_template with our custom function*/
    add_filter('single_template', 'my_custom_template');

    function my_custom_template($single) {

        global $post;

        /* Checks for single template by post type */
        if ( $post->post_type == 'tools' ) {
            if ( file_exists( plugin_dir_path( __FILE__ ) . '/single-tools.php' ) ) {
                return plugin_dir_path( __FILE__ ) . '/single-tools.php';
            }
        }

        return $single;

    }

   
  }
   
  /* Hook into the 'init' action so that the function
  * Containing our post type registration is not 
  * unnecessarily executed. 
  */
   
  add_action( 'init', 'custom_post_type', 0 );


