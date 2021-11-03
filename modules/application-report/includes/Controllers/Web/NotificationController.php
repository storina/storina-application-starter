<?php

namespace VOS\Controllers\Web;

use VOS\Models\Viewer;
use \STORINA\Controllers\General;

class NotificationController {

    public $service_provider;

    const notif_url = "https://fcm.googleapis.com/fcm/send";
    const google_secret_key = "AAAAph5EJno:APA91bE9wjWPSoECBBQ0ndUCCaPMgUfXHC1sJOy3n9maXTzntlHOVGt9wwGIOGLy2PWDLl5R2ZUkf6cuuNsqClCXcIzmjfshbu8X35dCVVJOTsX4KX_ak2WFh-2RGUHTEBOMa-gK6EgW";
    const user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13";
    const notification_action = 'osa_report_notification_action';

    public function __construct($service_provider){
        $this->service_provider = $service_provider;
        add_action('wp_ajax_' . self::notification_action,[$this,'send_notification']);
    }

    public function get_notification_handler(){
        global $osa_autoload;
        return $osa_autoload->service_provider->get(General::class);
    }

    public function send_notification(){
        $identifier = sanitize_text_field($_POST['identifier']);
        if(!is_numeric($identifier) || !empty(sanitize_text_field($_POST['title'])) || !empty(sanitize_text_field($_POST['body']))){
            wp_send_json([
                'status' => false,
                'message' => esc_html__("params is invalid","onlinerShopApp")
            ]);
        }
        $notification_action = $this->get_notification_handler()->clickEvent(sanitize_text_field($_POST['click_event_type']),sanitize_text_field($_POST['click_event_value']));
        $data = array_merge([
            'title' => sanitize_text_field($_POST['title']),
            'body' => sanitize_text_field($_POST['body']),
            'icon' => sanitize_text_field($_POST['notification_icon']);
            'sound' => 'default',
            'badge' => 1,
            ],$notification_action);
        $viewer = Viewer::get_viewer_by_identifier($identifier);
        $notif_token = $viewer->get_data('notif_token');
        $post_fields = json_encode(array('to' => $notif_token, 'priority' => 'high', "data" => $data));
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=' . self::google_secret_key
        );
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, self::notif_url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_USERAGENT, self::user_agent);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields);
        $result = json_decode(curl_exec($curl));
        $status = filter_var($result->success,FILTER_VALIDATE_BOOLEAN);
        curl_close($curl);
        wp_send_json([
            'status' => filter_var($result->success,FILTER_VALIDATE_BOOLEAN),
            'message' => ($status)? esc_html__("notification was sended","onlinerShopApp") : esc_html__("error","onlinerShopApp"),
            'result' => $result,
        ]);
    }

}
