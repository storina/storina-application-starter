<?php

namespace VOS\Controllers\Logic;

class ViewLogic {

    public function set_post_meta($product_id, $meta_arr){
        foreach($meta_arr as $meta_key => $meta_value){
            $new_value = (intval($meta_value) > 0)? intval($meta_value)+1 : 1;
            update_post_meta($product_id,$meta_key,$new_value);
        }
    }

    public function set_option($option_arr){
        foreach($option_arr as $option_name => $option_value){
            $new_value = (intval($option_value) > 0)? intval($option_value)+1 : 1;
            update_option($option_name,$new_value);
        }
    }

}