<?php 

namespace CRN\Controllers\Logic;

class NotifAction {

    public $service_provider;

    const notif_url = "https://fcm.googleapis.com/fcm/send";
    const google_secret_key = "AAAAph5EJno:APA91bE9wjWPSoECBBQ0ndUCCaPMgUfXHC1sJOy3n9maXTzntlHOVGt9wwGIOGLy2PWDLl5R2ZUkf6cuuNsqClCXcIzmjfshbu8X35dCVVJOTsX4KX_ak2WFh-2RGUHTEBOMa-gK6EgW";
    const user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13";
    
    public function __construct($service_provider){
        $this->service_provider = $service_provider;
    }

    public function send_notif($notif_token,$data){
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
        curl_close($curl);
        return $result;
    }
    
}