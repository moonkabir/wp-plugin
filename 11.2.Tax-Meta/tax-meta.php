<?php
/*
Plugin Name: Tax Meta
Plugin URI: https://github.com/moonkabir/wp-plugin
Description: WordPress Tax Meta Field
Version: 1.0
Author: Moon Kabir
Author URI: https://moonkabir.xyz
License: GPLv2 or later
Text Domain: tax-meta
Domain Path: /languages/
*/

function taxm_load_textdomain(){
    load_plugin_textdomain('tax-meta',false,dirname(__FILE__)."/languages");
}
add_action('plugins_loaded','taxm_load_textdomain');

function taxm_bootstarp(){
    $arguments = array(
        'type'=>'string',
        'sanitize_callbcak'=>'sanitize_text_field',
        'single'=>true,
        'description'=>'sample meta field for category tax',
        'show_in_rest'=>true,
    );
    register_meta('term','taxm_extra_info',$arguments);
}
add_action('init','taxm_bootstarp');

function taxm_category_form_field(){
    // $extra_info = get_term_meta($term_id,'taxm_extra_info',true);
    ?>
    <div class="form-field form-required term-name-wrap">
        <label for="tag-name"><?php _e('Extra Info','tax-meta'); ?></label>
        <input name="extra-info" id="extra-info" type="text" value="<?php //echo esc_attr($extra_info);?>" size="40" aria-required="true">
        <p><?php _e('Some help text','tax-meta'); ?>.</p>
    </div>
    <?php
}
add_action('category_add_form_fields','taxm_category_form_field');
add_action('post_tag_add_form_fields','taxm_category_form_field');
add_action('genre_add_form_fields','taxm_category_form_field');

function taxm_category_edit_form_field($term){
    $extra_info = get_term_meta($term->term_id,'taxm_extra_info',true);
    ?>
        <tr class="form-field form-required term-name-wrap">
            <th scope="row">
                <label for="tag-name"><?php _e('Extra Info','tax-meta'); ?></label>
            </th>
            <td>
                <input name="extra-info" id="extra-info" type="text" value="<?php echo esc_attr($extra_info);?>" size="40" aria-required="true">
                <p><?php _e('Some help text','tax-meta'); ?>.</p>
            </td>
        </tr>
    <?php
}
add_action('category_edit_form_fields','taxm_category_edit_form_field');
add_action('post_tag_edit_form_fields','taxm_category_edit_form_field');
add_action('genre_edit_form_fields','taxm_category_edit_form_field');

function taxm_save_category_meta($term_id){
    if(wp_verify_nonce($_POST['_wpnonce_add-tag'],'add-tag')){
        $extra_info = sanitize_text_field($_POST['extra-info']);
        update_term_meta($term_id,'taxm_extra_info',$extra_info);
    }
}
add_action('create_category','taxm_save_category_meta');
add_action('create_post_tag','taxm_save_category_meta');
add_action('create_genre','taxm_save_category_meta');

function taxm_update_category_meta($term_id){
    if(wp_verify_nonce($_POST['_wpnonce'],"update-tag_{$term_id}")){
        $extra_info = sanitize_text_field($_POST['extra-info']);
        update_term_meta($term_id,'taxm_extra_info',$extra_info);
    }
}
add_action('edit_category','taxm_update_category_meta');
add_action('edit_post_tag','taxm_update_category_meta');
add_action('edit_genre','taxm_update_category_meta');