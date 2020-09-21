<?php
/*
Plugin Name: Our MetaBox
Plugin URI: 
Description: 
Version: 1.0
Author: 
Author URI: 
License: GPLv2 or later
Text Domain: our-metabox
Domain Path: /languages/
*/
class OurMetabox{
    public function __construct(){
        add_action('plugins_loaded',array($this,'omb_load_textdomain'));
        add_action('admin_menu',array($this,'omb_add_metabox'));
        add_action('save_post',array($this,'omb_save_metabox'));
        add_action('save_post',array($this,'omb_save_image'));
        add_action('save_post',array($this,'omb_save_gallery'));


        add_action('admin_enqueue_scripts',array($this,'omb_admin_assets'));
        add_filter('user_contactmethods',array($this,'omb_use_contact_methods'));
    }

    function omb_use_contact_methods($methods){
        $methods['facebook'] = __('facebook','our-metabox');
        $methods['linkedin'] = __('linkedin','our-metabox');
        $methods['twitter'] = __('twitter','our-metabox');

        // for showing this data 
        // echo esc_url(get_the_author_meta('facebook'));

        return $methods;
    }

    function omb_admin_assets(){
        wp_enqueue_style('omb_admin_style',plugin_dir_url(__FILE__)."/assets/admin/css/style.css",null,time());
        wp_enqueue_style('jquery-ui-css','//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css',null,time());
        wp_enqueue_script('omb_admin_js',plugin_dir_url(__FILE__)."/assets/admin/js/main.js",array('jquery','jquery-ui-datepicker'),time(),true);
    }

    private function is_secured($nonce_field,$action,$post_id){
        $nonce = isset($_POST[$nonce_field])?$_POST[$nonce_field]:'';
        if($nonce == ''){
            return false;
        }
        if(!wp_verify_nonce($nonce,$action)){
            return false;
        }
        if(current_user_can('edit-post',$post_id)){
            return false;
        }
        if(wp_is_post_autosave($post_id)){
            return false;
        }
        if(wp_is_post_revision($post_id)){
            return false;
        }
        return true;
    }

    function omb_save_image($post_id){
        if(!$this->is_secured('omb_image_nonce','omb_image',$post_id)){
            return $post_id;
        }
        $image_id = isset($_POST['omb_image_id'])?$_POST['omb_image_id']:'';
        $image_url = isset($_POST['omb_image_url'])?$_POST['omb_image_url']:'';

        update_post_meta($post_id,'omb_image_id',$image_id);
        update_post_meta($post_id,'omb_image_url',$image_url);

    }
    
    function omb_save_gallery($post_id){
        if(!$this->is_secured('omb_gallery_nonce','omb_gallery',$post_id)){
            return $post_id;
        }
        $image_id = isset($_POST['omb_images_id'])?$_POST['omb_images_id']:'';
        $image_url = isset($_POST['omb_images_url'])?$_POST['omb_images_url']:'';

        update_post_meta($post_id,'omb_images_id',$image_id);
        update_post_meta($post_id,'omb_images_url',$image_url);

    }

    
    function omb_save_metabox($post_id){
        if(!$this->is_secured('omb_location_field','omb_location',$post_id)){
            return $post_id;
        }

        $location = isset($_POST['omb_location'])?$_POST['omb_location']:'';
        $country = isset($_POST['omb_country'])?$_POST['omb_country']:'';
        $is_favourite = isset($_POST['omb_is_favourite'])?$_POST['omb_is_favourite']:0;
        $colors = isset($_POST['omb_clr'])?$_POST['omb_clr']:array();
        $colors2 = isset($_POST['omb_color'])?$_POST['omb_color']:array();
        $fav_color = isset($_POST['omb_fav_color'])?$_POST['omb_fav_color']:'';

        // if($location=='' || $country ==''){
        //     return $post_id;
        // }

        $location = sanitize_text_field($location);
        $country = sanitize_text_field($country);


        update_post_meta($post_id,'omb_location',$location);
        update_post_meta($post_id,'omb_country',$country);
        update_post_meta($post_id,'omb_is_favourite',$is_favourite);
        update_post_meta($post_id,'omb_clr',$colors);
        update_post_meta($post_id,'omb_color',$colors2);
        update_post_meta($post_id,'omb_fav_color',$fav_color);
        
    }

    function omb_add_metabox(){
        add_meta_box(
            'omb_post_location',
            __('location Info','our-metabox'),
            array($this,'omb_display_metabox'),
            array('post','page')
            // 'side'
        );
        add_meta_box(
            'omb_book_info',
            __('Book Info','our-metabox'),
            array($this,'omb_book_info'),
            array('book')
            // 'side'
        );
        add_meta_box(
            'omb_image_info',
            __('Image Info','our-metabox'),
            array($this,'omb_image_info'),
            array('post')
            // 'side'
        );
        add_meta_box(
            'omb_gallery_info',
            __('Gallery Info','our-metabox'),
            array($this,'omb_gallery_info'),
            array('post')
            // 'side'
        );
    }

    function omb_book_info($post){
        wp_nonce_field('omb_book','omb_book_nonce');

        $metabox_html =<<<EOD
<div class="fields">
    <div class="field_c">
        <div class="label_c">
            <label for="book_author">Book Author</label>
        </div>
        <div class="input_c">
            <input type="text" class="widefat" id="book_author">   
        </div>
        <div class="float_c"></div>
    </div>
    
    <div class="field_c">
        <div class="label_c">
            <label for="book_isbn">Book ISBN</label>
        </div>
        <div class="input_c">
            <input type="text" id="book_isbn">   
        </div>
        <div class="float_c"></div>
    </div>

    <div class="field_c">
        <div class="label_c">
            <label for="book_year">Publish Year</label>
        </div>
        <div class="input_c">
            <input type="text" class="omb_dp" id="book_year">   
        </div>
        <div class="float_c"></div>
    </div>
    
</div
EOD;

        echo $metabox_html;

    }

    function omb_image_info($post){
        $image_id = esc_attr(get_post_meta($post->ID,'omb_image_id',true));
        $image_url = esc_attr(get_post_meta($post->ID,'omb_image_url',true));
        wp_nonce_field('omb_image','omb_image_nonce');
        $button_label = __("Upload Image",'our-metabox');
        $metabox_html =<<<EOD
        <div class="fields">
            <div class="field_c">
                <div class="label_c">
                    <label>Image</label>
                </div>
                <div class="input_c">
                    <button class="button" id="upload_image">{$button_label}</button>
                    <input type="hidden" name="omb_image_id" id="omb_image_id" value="{$image_id}"/>
                    <input type="hidden" name="omb_image_url" id="omb_image_url" value="{$image_url}"/>
                    <div id="image-container">
                    </div>
                </div>
                <div class="float_c"></div>
            </div>    
        </div
EOD;
        echo $metabox_html;

    }

    function omb_gallery_info($post){
        $image_id = esc_attr(get_post_meta($post->ID,'omb_images_id',true));
        $image_url = esc_attr(get_post_meta($post->ID,'omb_images_url',true));
        wp_nonce_field('omb_gallery','omb_gallery_nonce');
        
        $label = __("Gallery",'our-metabox');
        $button_label = __("Upload Images",'our-metabox');

        $metabox_html =<<<EOD
        <div class="fields">
            <div class="field_c">
                <div class="label_c">
                    <label>{$label}</label>
                </div>
                <div class="input_c">
                    <button class="button" id="upload_images">{$button_label}</button>
                    <input type="hidden" name="omb_images_id" id="omb_images_id" value="{$image_id}"/>
                    <input type="hidden" name="omb_images_url" id="omb_images_url" value="{$image_url}"/>
                    <div id="images-container">
                    </div>
                </div>
                <div class="float_c"></div>
            </div>    
        </div
EOD;
        echo $metabox_html;

    }



    function omb_display_metabox($post){
        $location = get_post_meta($post->ID,'omb_location',true);
        $country = get_post_meta($post->ID,'omb_country',true);
        $is_favourite = get_post_meta($post->ID,'omb_is_favourite',true);
        $checked = $is_favourite==1?'checked':'';
        $saved_colors = get_post_meta($post->ID,'omb_clr',true);
        $saved_color = get_post_meta($post->ID,'omb_color',true);

        $label1=__('Location','our-metabox');
        $label2=__('Country','our-metabox');
        $label3=__('Is Favourite','our-metabox');
        $label4=__('Colors','our-metabox');

        $colors=array('red','yellow','black','magenta','pink','green','blue');

        wp_nonce_field('omb_location','omb_location_field');
        $metabox_html = <<<EOD
<p>
    <label for="omb_location">{$label1}</label>
    <input type="text" name="omb_location" id="omb_location" value="{$location}"/>
    <br/>
    <label for="omb_country">{$label2}</label>
    <input type="text" name="omb_country" id="omb_country" value="{$country}"/>
</p>

<p>
    <label for="omb_is_favourite">{$label3}</label>
    <input type="checkbox" name="omb_is_favourite" id="omb_is_favourite" value="1" {$checked}/>
</p>

<p>
    <label>{$label4}:</label>

EOD;
        $saved_colors = is_array($saved_colors)?$saved_colors:array();
        foreach($colors as $color){
            $_color = ucwords($color);
            $checked = in_array($color,$saved_colors)?'checked':'';
            $metabox_html .=<<<EOD
<label for="omb_clr_{$color}">{$_color}</label>
<input type="checkbox" name="omb_clr[]" id="omb_clr_{$color}" value="{$color}" {$checked}/>

EOD;

        }

        $metabox_html .="</p>";
        $metabox_html .=<<<EOD
<p>
    <label>{$label4}:</label>
EOD;
    foreach($colors as $color){
        $_color = ucwords($color);
        $checked = ($color==$saved_color)?"checked='checked'":'';
        $metabox_html .=<<<EOD
    <label for="omb_color_{$color}">{$_color}</label>
    <input type="radio" name="omb_color" id="omb_color_{$color}" value="{$color}" $checked />

EOD;

    }


    $metabox_html .="</p>";
    $fav_color = get_post_meta($post->ID,'omb_fav_color',true);
    
    $dropdown_html = "<option value='0'>".__("choose a color","our-metabox")."</option>";
    foreach($colors as $color){
        $selected = '';
        if($color == $fav_color){
            $selected = 'selected';
        }
        $dropdown_html .= sprintf("<option %s value='%s'>%s</option>",$selected,$color,ucwords($color));
    }

    $metabox_html .=<<<EOD
    <p>
        <label for="omb_fav_color">{$label4}:</label>
        <select name="omb_fav_color" id="omb_fav_color">
            {$dropdown_html}
        </select>
    </p>


EOD;

        echo $metabox_html;

    }

    function omb_load_textdomain(){
        load_plugin_textdomain('our-metabox',false,dirname(__FILE__)."/languages");
    }
        
}

new OurMetabox();