<?php 

namespace CRN\Controllers\Api;

use WC_Product;
use CRN\Controllers\Web\UserController;

class Product {

    public $service_provider;
    public $user_controller;

    public function __construct($service_provider){
        $this->service_provider = $service_provider;
        $this->user_controller = $this->service_provider->get(UserController::class);
    }

    public function update(){
        $user_id = $this->user_controller->get_user_by_token($_POST['userToken']);
        $authentication = apply_filters('crn_authentication_middleware',['status'=>true],$user_id);
		if(!$authentication['status']){
			return $authentication;
		}
        $product_id = $_POST['product_id'];
        $stock_status = $_POST['stock_status'];
        $product = wc_get_product($product_id);
        if(false == $product instanceof WC_Product){
            return(array(
                "status" => false,
                "message" => __("invalid product","crn")
            ));
        }
        $vendor_id = get_post_field( 'post_author', $product_id );
        if($vendor_id != $user_id){
            return(array(
                "status" => false,
                "message" => __("invalid vendor","crn")
            ));
        }
        $parent_id = (isset($_POST['id']))? $_POST['id'] : null;
        if($product->is_type('variation') && isset($parent_id)){
            $product_parent = wc_get_product($parent_id);
            if($product_parent instanceof WC_Product){
                $product_parent->set_manage_stock(false);
                $product_parent->save();
            }
        }
        if($product->is_type('variable')){
            $child_ids = $product->get_children();
            foreach($child_ids as $child_id){
                $child = wc_get_product($child_id);
                $child->set_manage_stock(false);
                $child->set_stock_status($stock_status);
                $child->save();
            }
        }else{
            $product->set_manage_stock(false);
            $product->set_stock_status($stock_status);
            $result = $product->save();
        }
        return(array(
            "status" => true,
            "message" => __("product status updated","crn")
        ));
    }
}
