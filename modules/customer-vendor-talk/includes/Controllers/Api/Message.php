<?php 

namespace CRN\Controllers\Api;

use OSA_JDate;
use WC_Product;
use CRN\Models\Message as MessageModel;
use CRN\Controllers\Logic\UserLogic;
use CRN\Controllers\Logic\AttachmentAction;
use CRN\Controllers\Logic\MessageAction;
use CRN\Controllers\Web\UserController;

class Message {

    public $service_provider;
    public $user_controller;
    public $attachment_action;
    public $message_action;
    public $user_logic;
    
    const message_per_page = 50;
    const chats_per_page = 10;
    
    public function __construct($service_provider){
        $this->service_provider = $service_provider;
        $this->user_logic = $service_provider->get(UserLogic::class);
        $this->user_controller = $service_provider->get(UserController::class);
        $this->attachment_action = $service_provider->get(AttachmentAction::class);
        $this->message_action = $service_provider->get(MessageAction::class);
    }

    public function new(){
        $owner_id = $this->user_controller->get_user_by_token($_POST['userToken']);
        $authentication = apply_filters('crn_authentication_middleware',['status'=>true],$owner_id);
	if(!$authentication['status']){
		return $authentication;
	}
        $data = array(
            "customer_id" => $_POST['customer_id'],
            "vendor_id" => $_POST['vendor_id'],
            'product_id' => $_POST['product_id'],
            "content" => $_POST['content'],
            "attachment_slug" => null,
            "owner_id" => $owner_id,
            'updated_at' => time(),
            "seen" => (isset($_POST['seen']))? $_POST['seen'] : 0,
        );
        if(!empty($_FILES['attachment']['name'])){
            $attachment_slug = $this->attachment_action->upload($_FILES['attachment']);
            $data['attachment_slug'] = $attachment_slug;
        }
        $message = new MessageModel($data);
        $message_id = $message->save();
        $message = MessageModel::get($message_id);
        if(is_wp_error($message_id) || false == $message instanceof MessageModel){
            $error = $message_id;
            return(array(
                "status" => false,
                "messages" => $error->get_error_message()
            ));
        }
        $notif_result = $this->message_action->send_notif($_POST,$owner_id);
        $attachment_slug_path = $message->get_data("attachment_slug");
        $attachment_slug = trailingslashit( home_url() ) . $attachment_slug_path;
        $message->set_data(array("attachment_slug" => (!empty($attachment_slug_path))? $attachment_slug : ""));
        return(array(
            "status" => true,
            'data' => [
                'message_id' => $message->id,
                'message_data' => $this->message_action->prepare_message_data($message,$owner_id),
                'notif_result' => $notif_result
            ],
            "message" => __("message send","crn")
        ));
    }

    public function all(){
        $user_id = (int) $this->user_controller->get_user_by_token($_POST['userToken']);
        $customer_id = (int) $_POST['customer_id'];
        $vendor_id = (int) $_POST['vendor_id'];
        $product_id = (int) $_POST['product_id'];
        $paged =(int) $_POST['paged'];
        $per_page =(int) self::message_per_page;
        $start = (int) ($paged-1)*$per_page;
        $end = (int) ($per_page*$paged)-1;
        $messages = MessageModel::get_message_list($customer_id,$vendor_id,$product_id,$start,$end);
        if(empty($messages)){
            return(array(
                "status" => false,
                "messages" => __("No Message Found","crn")
            ));
        }
        $messages = $this->message_action->set_messages_seen($messages,$user_id);
        $message_list = array();
        foreach($messages as $message){
            $attachment_slug_path = $message->get_data("attachment_slug");
            $attachment_slug = trailingslashit( home_url() ) . $attachment_slug_path;
            $message->set_data(array("attachment_slug" => (!empty($attachment_slug_path))? $attachment_slug : ""));
            $message_list[] = $this->message_action->prepare_message_data($message,$user_id);
        }
        return($message_list);
    }

    public function user() {
        $user_id = (int) $this->user_controller->get_user_by_token($_POST['userToken']);
        $authentication = apply_filters('crn_authentication_middleware',['status'=>true],$user_id);
	if(!$authentication['status']){
		return $authentication;
	}
        $paged =(int) $_POST['paged'];
        $per_page =(int) self::message_per_page;
        $start = (int) ($paged-1)*$per_page;
        $end = (int) ($per_page*$paged)-1;
        $message_rows = MessageModel::get_user_message_list($user_id,$start,$end);
        $data = [];
        foreach($message_rows as $message_row){
            $product_id = $message_row['product_id'];
            $product = wc_get_product($product_id);
            if(false == $product instanceof WC_Product){
                continue;
            }
            $message_ids = explode(',', $message_row['message_ids']);
            $message_id_end = current($message_ids);
            $message = MessageModel::get($message_id_end);
            unset($message_row['product_id'],$message_row['created_at'],$message_row['message_ids']);
            $message_row['updated_at'] = strtotime($message_row['updated_at']);
            $product_info = [
                'product_id' => $product->get_id(),
                'title' => $product->get_name(),
                'image' => current(wp_get_attachment_image_src($product->get_image_id(),'thumbnail')),
                'stock_status' => $product->get_stock_status(),
            ];
            $message_row['product_info'] = $product_info;
            $message_row['message'] =  $this->message_action->prepare_message_data($message,$user_id);
            $contact_data = $this->user_logic->get_message_contact_data($message_row,$user_id);
            $message_row['title'] = $contact_data['store_name'] ?: $contact_data['display_name'];
            $data[] = $message_row;
        }
        return([
            'status' => true,
            'data' => $data,
        ]);
    }

}
