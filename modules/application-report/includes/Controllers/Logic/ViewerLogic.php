<?php

namespace VOS\Controllers\Logic;

use WP_User;
use VOS\Models\Viewer;

class ViewerLogic {

    public function prepare_viewers_data($viewers){
        if(empty($viewers)){
            return [];
        }
        foreach($viewers as $viewer){
            $user = (false == $viewer->get_data('authentication'))? null : get_user_by( 'ID', $viewer->get_data('identifier') );
            if($user instanceof WP_User){
                $user_id = $user->ID;
                $first_name = get_user_meta($user_id,'first_name',true);
                $last_name = get_user_meta($user_id,'last_name',true);
                $nickname = get_user_meta($user_id,'nickname',true);
                $display_name = (empty($first_name) && empty($last_name))? $nickname : "{$first_name} {$last_name}";
            }else{
                $display_name = __("guest","onlinerShopApp");
            }
            $viewer_details['display_name'] = $display_name;
            $viewer_details['identifier'] = $viewer->get_data('identifier');
            $viewer_details['authentication'] = ($viewer->get_data('authentication'))? __("yes","onlinerShopApp") : __("no","onlinerShopApp");
            $viewer_details['authentication_value'] = $viewer->get_data('authentication');
            $viewer_details['client_type'] = $viewer->get_data("client_type");
            $viewer_details['current_version'] = $viewer->get_data('current_version');
            $viewer_output[] = $viewer_details;
        }
        return $viewer_output ?? [];
    }
}