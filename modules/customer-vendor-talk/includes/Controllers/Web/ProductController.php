<?php 

namespace CRN\Controllers\Web;

class ProductController {
    
    public $service_provider;
    public $user_controller;

    public function __construct($service_provider){
        $this->user_controller = $service_provider->get(UserController::class);
        add_filter("osa_single_get_vendor_info",array($this,"add_chat_initializer"),10,3);
        add_filter('woap_single_data_spmv_other_products',[$this,'add_spmv_chat_initializer']);
    }

    public function add_chat_initializer($vendor_info,$vendor_id,$user_id){
        if(is_numeric($user_id) && is_numeric($vendor_id) && $user_id != $vendor_id){
            $vendor_info["crn_chat_info"] = array(
                "customer_id" => intval($user_id),
                "vendor_id" => intval($vendor_id),
            );
        }
        return $vendor_info;
    }

    public function add_spmv_chat_initializer($multi_vendor_products){
        $user_token = sanitize_text_field($_POST['userToken']) ?: null;
        if(empty($multi_vendor_products) || !isset($user_token)){
            return $multi_vendor_products;
        }
        $customer_id = $this->user_controller->get_user_by_token($user_token);
        foreach($multi_vendor_products as &$product_resource){
            $product_id = $product_resource['id'];
            $vendor_id = get_post_field('post_author',$product_id);
            $product_resource['crn_chat_info'] = [
                'customer_id' => $customer_id,
                'vendor_id' => $vendor_id
            ];
        }
        return $multi_vendor_products;
    }

}
