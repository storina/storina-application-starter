<?php 

namespace CRN\Controllers\Logic;

use WP_User;

class UserLogic {

    public function get_message_contact_data($message_row,$user_id){
        return ($user_id == $message_row['customer_id'])? 
            $this->prepare_user_data($message_row['vendor_id'],'vendor') :
            $this->prepare_user_data($message_row['customer_id']);
    }

    public function prepare_user_data($user_id,$user_role='customer'){
        $user = get_user_by( 'ID', $user_id );
        if(false == $user instanceof WP_User){
            return [];
        }
        $first_name = $user->first_name;
        $last_name = $user->last_name;
        $display_name = $user->display_name;
        $full_name = "{$first_name} {$last_name}";
        $user_data = [
            'id' => $user_id,
            'display_name' => ($full_name) ?: $display_name
        ];
        if('vendor' == $user_role){
            $store_info = dokan_get_store_info($user_id);
            $user_data['store_name'] = $store_info['store_name'];
        }
        return $user_data;
    }

}