<?php
/*
Plugin Name: Post And Taxonomy Selector
Plugin URI: https://github.com/moonkabir/wp-plugin
Description: WordPress Tax Meta Field
Version: 1.0
Author: Moon Kabir
Author URI: https://moonkabir.xyz
License: GPLv2 or later
Text Domain: post-tag-metafield
Domain Path: /languages/
*/

function ptmf_init(){
    add_action('admin-enqueue_scripts','ptmf_admin_assets');
}

add_action('admin-init','ptmf_init');

function ptmf_load_textdomain(){
    load_plugin_textdomain('post-tag-metafield',false,dirname(__FILE__)."/languages");
}

add_action('plugins_loaded','ptmf_load_textdomain');

function ptmf_admin_assets(){
    wp_enqueue_style('omb_admin_style',plugin_dir_url(__FILE__)."/assets/admin/css/style.css",null,time());
}

function ptmf_add_metabox(){
    add_meta_box(
        'ptmf_select_posts_mb',
        __('Select Posts','post-tag-metafield'),
        'ptmf_display_metabox',
        array('page')
    );
}

add_action('admin_menu','ptmf_add_metabox');

function ptmf_save_metabox($post_id){
    if(!ptmf_is_secured('ptmf_posts_nonce','ptmf_posts',$post_id)){
        return $post_id;
    }
    
    $selected_post_id = $_POST['ptmf_posts'];
	$selected_term_id = $_POST['ptmf_term'];
    if($selected_post_id>0){
        update_post_meta($post_id,'ptmf_selected_posts',$selected_post_id);
    }

    if ( $selected_term_id > 0 ) {
		update_post_meta( $post_id, 'ptmf_selected_term', $selected_term_id );
	}
    return $post_id;
}

add_action('save_post','ptmf_save_metabox');

function ptmf_display_metabox($post){

    $selected_post_id = get_post_meta($post->ID,'ptmf_selected_posts',true);
    $selected_term_id = get_post_meta($post->ID,'ptmf_selected_term',true);
    
    wp_nonce_field('ptmf_posts','ptmf_posts_nonce');

    $arg = array(
        'posts_type'=>'post',
        'posts_per_pages'=>-1
    );

    $dropdown_list = '';
    $_posts = new WP_Query($arg);
    while($_posts->have_posts()){
        $extra = '';
        $_posts->the_post();
        if(in_array(get_the_ID(),$selected_post_id)){
            $extra = 'selected';
        }
        $dropdown_list .= sprintf("<option %s value='%s'>%s</option>",$extra,get_the_ID(),get_the_title());
    }
    wp_reset_query();

    $_terms = get_terms(array(
    	'taxonomy'=>'genre',
	    'hide_empty' => false,
    ));

	$term_dropdown_list = '';
    foreach($_terms as $_term){
	    $extra = '';
	    if($_term->term_id == $selected_term_id){
		    $extra = 'selected';
	    }
	    $term_dropdown_list .= sprintf( "<option %s value='%s'>%s</option>", $extra, $_term->term_id, $_term->name );
    }


    $label = __("Select Posts",'post-tag-metafield');
    $label2 = __("Select term",'post-tag-metafield');
    $metabox_html =<<<EOD
    <div class="fields">
        <div class="field_c">
            <div class="label_c">
                <label>{$label}</label>
            </div>
            <div class="input_c">
                <select multiple = "multiple" name="ptmf_posts[]" id="ptmf_posts">
                    <option value="0">{$label}</option>
                    {$dropdown_list}
                </select>
            </div>
            <div class="float_c"></div>
        </div>    

        <div class="field_c">
            <div class="label_c">
                <label for="ptmf_term">{$label2}</label>
            </div>
            <div class="input_c">
                <select name="ptmf_term" id="ptmf_term"> 
                    <option value="0">{$label2}</option>
				    {$term_dropdown_list}
                </select>
            </div>
            <div class="float_c"></div>
        </div>    

    </div
EOD;

    echo $metabox_html;
}

if ( ! function_exists( 'ptmf_is_secured' ) ) {
	function ptmf_is_secured( $nonce_field, $action, $post_id ) {
		$nonce = isset( $_POST[ $nonce_field ] ) ? $_POST[ $nonce_field ] : '';

		if ( $nonce == '' ) {
			return false;
		}
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		if ( wp_is_post_autosave( $post_id ) ) {
			return false;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return false;
		}

		return true;

	}
}