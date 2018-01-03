<?php

/*
Plugin Name: Simple Custom Fields
Plugin URL: NA
Description: A simple plugin to add custom fields to a specific page
Version: 1.0
Author: NA
Author URI: NA
Text Domain: scf_plugin
Domain Path: languages
*/

if( !class_exists('SimpleCustomFields') ):

class SimpleCustomFields {

  // The slug of the page/post to add custom fields
  private $post_path = 'test-page';

  // The header label for the custom fields form
  private $header_label = 'Page custom fields';

  public function __construct(){
    add_action( 'admin_init', array($this, 'init_custom_fields_form') );
  }

  public function init_custom_fields_form(){
    $post_path_is_set = $this->post_path_is_set();
    $editing_selected_post = $this->editing_selected_post();

    if ($post_path_is_set) {
      if ($editing_selected_post) {
        add_action( 'add_meta_boxes', array($this, 'scf_register_meta_boxes') );
      }
    }else{
      add_action( 'admin_notices', array($this, 'show_scf_post_path_error') );
    }
  }

  public function scf_register_meta_boxes(){
    add_meta_box('content_fields', 'Custom Fields', array($this, 'render_admin_form'));
  }

  public function show_scf_post_path_error(){
    ?>
      <div class="error notice">
        <p>
          Simple Custom Fields: Post path not set
        </p>
      </div>
    <?php
  }

  public function render_admin_form(){
    $nonce_name = "scf_nonce_" . $this->post_path;
    wp_nonce_field( $nonce_name, $nonce_name );
      ?>
      <table class="form-table scf_table">

      </table>
      <?php
  }

  private function post_path_is_set(){
    return isset($this->post_path);
  }

  private function editing_selected_post(){
    $current_post_id = $this->get_post_id();
    $selected_post_id = get_page_by_path($this->post_path)->ID;
    return $current_post_id == $selected_post_id;
  }

  private function get_post_id(){
    $post_id = (isset($_GET['post']) ? $_GET['post'] : 0);
    return $post_id;
  }

}

new SimpleCustomFields();

endif;

?>
