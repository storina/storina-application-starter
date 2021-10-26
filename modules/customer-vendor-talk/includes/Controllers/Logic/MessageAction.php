<?php

namespace CRN\Controllers\Logic;

use \STORINA\Libraries\JDate;
use WC_Product;
use CRN\Models\Message;
use CRN\Controllers\Logic\NotifAction;

class MessageAction {

    public $service_container;    
    public $notif_action;

    public function __construct($service_container){
        $this->service_container = $service_container;
        $this->notif_action = $this->service_container->get(NotifAction::class);
    }

    public function set_messages_seen($messages,$user_id){
        $message_ids = [0];
        foreach($messages as &$message){
            $owner_id = $message->get_data('owner_id');
            if($user_id == $owner_id){
                continue;
            }
            $message_ids[] = $message->id;
            $message->set_data(['seen' => 1]);
        }
        Message::seen_messages($message_ids);
        return $messages;
    }

    public function send_notif($params,$owner_id){
        $customer_id = $params['customer_id'];
        $vendor_id = $params['vendor_id'];
        $other_id = ($customer_id == $owner_id)? $vendor_id : $customer_id;
        $product_id = $params['product_id'];
        $notif_token = get_user_meta($other_id,'notifToken',true);
        $excerpt = $this->custom_excerpt($params['content'],85);
        $data = [
            'customer_id' => $params['customer_id'],
            'vendor_id' => $params['vendor_id'],
            'product_id' => $params['product_id'],
            'product_name' => '',
            'excerpt' => $excerpt
        ];
        $product = wc_get_product($product_id);
        if($product instanceof WC_Product){
            $data['product_name'] = $product->get_name();
        }
        return $this->notif_action->send_notif($notif_token,$data);
    }

    public function custom_excerpt($content,$count){
        $output = strip_tags($content);
        $output = mb_substr($output , 0 , $count);
        $output = mb_substr($output , 0 , mb_strrpos($output, " "));
        $output .= (!empty($output))? "..." : '';
        return $output;
    }

    public function prepare_message_data($message,$user_id){
        $message_data = $message->get_data();
        $message_data['owner'] = ($message->get_data("owner_id") == $user_id);
        $message_data['updated_at_jalali_date'] = JDate::jdate('Y/m/d' , $message->get_data('updated_at'));
        $message_data['updated_at_jalali_time'] = JDate::jdate('H:i' , $message->get_data('updated_at'));
        $message_data['message_id'] = $message->id;
        return $message_data;
    }

}
