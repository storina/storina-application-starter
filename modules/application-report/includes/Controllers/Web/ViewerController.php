<?php

namespace VOS\Controllers\Web;

use VOS\Models\Viewer;

class ViewerController {

    const expire_plus = 300;

    public $service_provider;

    public function __construct($service_provider){
        $this->service_provider = $service_provider;
        add_action('osa_index_get_action_init',[$this,'viewer_action_run'],10,1);
    }

    public function viewer_action_run($user_id){
        $authentication = (is_numeric($user_id) && false != $user_id)? true : false;
        $identifier = (is_numeric($user_id) && false != $user_id)? $user_id : $_POST['googleID'];
        $viewer = Viewer::get_viewer_by_identifier($identifier);
        $expire_time = time() + self::expire_plus;
        $client_type = $_POST['client_type'] ?? '';
        $current_version = (isset($_POST['currentVersion']))? $_POST['currentVersion'] : null;
        $notif_token = $_POST['notifToken'] ?? 'empty result';
        if($viewer instanceof Viewer){
            $data = [
                'expire_time' => $expire_time,
                'current_version' => $current_version,
                'notif_token' => $notif_token,
                'client_type' => $client_type,
            ];
            $viewer->set_data($data)->save();
        }else{
            $data = [
                'identifier' => $identifier,
                'session_id' => 0,
                'action_slug' => 'index',
                'action_id' => 0,
                'authentication' => $authentication,
                'client_type' => $client_type,
                'expire_time' => $expire_time,
                'current_version' => $current_version,
                'notif_token' => $notif_token
            ];
            $viewer = new Viewer;
            $viewer->set_data($data)->save();
        }
    }

}