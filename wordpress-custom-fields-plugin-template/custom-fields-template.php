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

  // Class variables, do not edit directly, set in the __construct method
  private $post_path = '';
  private $header_label = '';
  private $fields = [];
  private $errors = [];

  public function __construct(){

    // BEGIN EDITABLE VALUES

    // The permalink slug of the page on which the fields should appear
    $this->set_post_slug('test-page');

    // Set a descriptive header for the custom fields form
    $this->set_form_header('Custom fields for this page');

    // Add custom fields here defined with underscores
    $this->add_custom_field('_custom_field_one');
    $this->add_custom_field('_custom_field_two');
    $this->add_custom_field('_custom_field_three');

    // END EDITABLE VALUES

    add_action( 'admin_init', array($this, 'init_custom_fields_form') );
    add_action( 'save_post', array($this, 'save_custom_fields') );
  }

  public function init_custom_fields_form(){

    // Validate plugin configuration
    if (!$this->post_path_is_set())
      $this->errors[] = 'Simple custom fields: Post path not set';

    if(!$this->form_header_is_set())
      $this->errors[] = 'Simple custom fields: Form header not set';

    if(!$this->custom_fields_are_set())
      $this->errors[] = 'Simple custom fields: Custom fields are not set';

    if ($this->plugin_is_configured()) {
      if ($this->editing_selected_post()) {
        add_action( 'add_meta_boxes', array($this, 'scf_register_meta_boxes') );
      }
    }else{
      add_action( 'admin_notices', array($this, 'display_error_messages') );
    }
  }

  public function scf_register_meta_boxes(){
    add_meta_box('content_fields', $this->header_label, array($this, 'render_admin_form'), $post);
  }

  public function display_error_messages(){
    foreach ($this->errors as $error_message) {
      ?>
        <div class="error notice">
          <p>
            <?php echo $error_message ?>
          </p>
        </div>
      <?php
    }
  }

  public function render_admin_form($post){
    wp_nonce_field( "scf_nonce", "scf_nonce" );
      ?>
      <table class="form-table scf_table">
        <?php foreach ($this->fields as $field): ?>
          <?php $label = $this->get_field_label($field) ?>
          <tr>
            <th>
              <label for="<?php echo $field ?>">
                <?php _e( $label, 'scf_plugin' ); ?>
              </label>
            </th>
            <td>
              <input
                type="text"
                name="<?php echo $field ?>"
                id="<?php echo $field ?>"
                value="<?php echo get_post_meta($post->ID, $field, true) ?>" class="regular-text" />
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
      <?php
  }

  public function save_custom_fields( $post_id ){

    // Only save values if we are editing a selected post
    if($this->get_selected_post_id() != $post_id)
      return;

    // verify nonce for security
    $nonce = $_POST['scf_nonce'];
    if ( empty( $nonce ) || !wp_verify_nonce( $nonce, "scf_nonce") )
        return;

    // don't save fields when doing autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
      return;

    // make sure the user is capable of editing this page
    if ( !current_user_can( 'edit_post', $post_id ) )
      return;

    // update each of the fields
    foreach ($this->fields as $field) {
      $cleaned_value = sanitize_text_field($_POST[$field]);
      update_post_meta( $post_id, $field,  $cleaned_value);
    }

  }

  private function set_post_slug($slug){
    $this->post_path = $slug;
  }

  private function set_form_header($header_label){
    $this->header_label = $header_label;
  }

  private function add_custom_field($field){
    $this->fields[] = $field;
  }

  private function form_header_is_set(){
    return isset($this->header_label) && !empty($this->header_label);
  }

  private function custom_fields_are_set(){
    return isset($this->fields) && !empty($this->fields);
  }

  private function plugin_is_configured(){
    return empty($this->errors);
  }

  private function get_field_label($field){
    $arr = explode('_', $field);
    $label = ucwords(implode(" ", $arr));
    return $label;
  }

  private function post_path_is_set(){
    return isset($this->post_path) && !empty($this->post_path);
  }

  private function get_post_id(){
    $post_id = (isset($_GET['post']) ? $_GET['post'] : 0);
    return $post_id;
  }

  private function editing_selected_post(){
    $current_post_id = $this->get_post_id();
    $selected_post_id = $this->get_selected_post_id();
    return $current_post_id == $selected_post_id;
  }

  private function get_selected_post_id(){
    return get_page_by_path($this->post_path)->ID;
  }

}

new SimpleCustomFields();

endif;

?>
