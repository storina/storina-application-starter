<?php 

add_action("init", function (){
    if(!isset($_POST['vendor_town']) || false == (strlen($_POST['vendor_town']) > 2) ){
        return;
    }
    $filters = array(
        "osa_spmv_other_products",
    );
    foreach($filters as $filter){
        add_filter($filter,function($query_args){
            $vendor_ids = storina_get_vendors_based_on_location();
            if(!empty($vendor_ids)){
                $query_args["author__in"] = $vendor_ids;
            }elseif(empty($vendor_ids)){
                $query_args['post__in'] = 0;
                $query_args['post_per_page'] = 0;
            }
            return $query_args;
        },10,1);
    }
} );

function storina_get_vendors_based_on_location(){
    if (!function_exists('dokan_is_seller_enabled')) {
        return array();
    }
    $count = - 1;
    $args = array(
        'role__in' => array('seller', 'administrator'),
        'number' => $count,
        'fields' => array('ID'),
    );
    $town = $_POST['vendor_town'];
    $AllVendors = get_users($args);
    $activeVendors = array();
    $type = osa_get_option('appVendorlist');
    $type = ( $type == 'state' ) ? $type : 'city';
    foreach ($AllVendors as $vendor) {
        if (dokan_is_seller_enabled($vendor->ID)) {
            if (strlen($town) == 0 OR ! $town) {
                $activeVendors[] = $vendor->ID;
            } else {
                $store_settings = dokan_get_store_info($vendor->ID);
                if ($store_settings['address'][$type] == $town) {
                    $activeVendors[] = $vendor->ID;
                }
            }
        }
    }

    return ( empty($activeVendors) ) ? array() : $activeVendors;
}

