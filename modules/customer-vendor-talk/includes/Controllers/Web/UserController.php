<?php

namespace CRN\Controllers\Web;

use WP_User;
use \STORINA\Controllers\User;
use CRN\Models\Message;

class UserController {

    public function __construct(){
        add_filter('osa_index_get_app_settings',array($this,'add_vendor_info'),10,1);
        add_filter('osa_index_get_app_settings',array($this,'add_message_status'),20,1);
    }

    public function add_vendor_info($app_settings){
        $user_id = $this->get_user_by_token($_POST['userToken']);
        $user = get_user_by( 'id', $user_id );
        $vendor_capability = "";
        if(is_numeric($user_id) || false != $user instanceof WP_User){
            $user_roles = $user->roles;
            $vendor_capability = (in_array('customer',$user_roles) || in_array('subscriber',$user_roles))? "" : $user_id;
        }
        $app_settings['vendor_capability'] = $vendor_capability;
        return $app_settings;
    }

    public function get_user_by_token($user_token){
        global $osa_autoload;
        $user_action = $osa_autoload->service_provider->get(User::class);
        $user_token = $_POST['userToken'];
        $user_id = $user_action->get_userID_byToken($user_token);
        return $user_id;
    }

    public function add_message_status($app_settings){
        $user_id = $this->get_user_by_token($_POST['userToken']);
        $user = get_user_by('id', $user_id);
        if($user instanceof WP_User){
            $user_roles = $user->roles;
            $column_name = (in_array('customer',$user_roles) || in_array('subscriber',$user_roles))? 'customer_id' : 'vendor_id';
            $column_value = $user_id;
            $user_info = [ $column_name => intval($column_value) ];
            $message_count = Message::get_unseen_messages_count($user_info);
            $app_settings['message_count'] = $message_count;
        }
        return $app_settings;
    }

}
