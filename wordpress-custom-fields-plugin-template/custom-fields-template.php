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
  protected $header_label = 'Page custom fields';

  // Fields to add to the form defined with underscores
  protected $fields = array(
                      '_custom_field_one',
                      '_custom_field_two',
                      '_custom_field_three');

  public function __construct(){
    add_action( 'admin_init', array($this, 'init_custom_fields_form') );
    add_action( 'save_post', array($this, 'save_custom_fields') );
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
    add_meta_box('content_fields', $this->header_label, array($this, 'render_admin_form'), $post);
  }

  public function show_scf_post_path_error($error){
    ?>
      <div class="error notice">
        <p>
          Simple Custom Fields: Post path not set
        </p>
      </div>
    <?php
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
      update_post_meta( $post_id, $field, $_POST[$field] );
    }

  }

  protected function get_field_label($field){
    $arr = explode('_', $field);
    $label = ucwords(implode(" ", $arr));
    return $label;
  }

  private function post_path_is_set(){
    return isset($this->post_path) && !empty($this->post_path);
  }

  protected function get_post_id(){
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
