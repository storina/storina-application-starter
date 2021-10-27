<?php

/**
 * prepare state id cities based on tapin state cities
 */
function storina_pws_prepare_tapin_cities() {
	if(!function_exists('PWS')){
		return [];
    }
    $zones = PWS_Tapin::zone();
    foreach($zones as $state_id => $state_resource){
        $city_resources[$state_id] = array_values($state_resource['cities']);
    }
    return $city_resources ?? [];
}

/**
 * prepare state id cities based on regular state_city taxonomy
 */
function storina_pws_prepare_regular_cities(){
	if(!function_exists('PWS')){
		return [];
    }
    $taxonomy = 'state_city';
    $args = [
        'taxonomy' => $taxonomy,
        'hide_empty' => false,
        'parent' => 0
    ];
    $states = get_terms($args);
    foreach($states as $state){
        $state_id = $state->term_id;
        $cities = get_terms( [
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'child_of'   => $state_id
        ] );
        $city_resources[$state_id] = array_values(array_column($cities,'name'));
    }
    return $city_resources ?? [];
}

/**
 * search city id from zone resource using city name
 */

function storina_pws_search_city_id($city_name,$zone){
    foreach($zone as $state_id => $state_resource){
        $cities = $state_resource['cities'];
        $city_id = array_search($city_name,$cities);
        if($city_id){
            return $city_id;
        }
    }
    return false;
}

/**
 * check if persian woocommerce shipping and tapin is enables
 */

function storina_pws_is_tapin_enabled(){
	if(!function_exists('PWS')){
		return false;
    }
    return (PWS_Tapin::is_enable())? true : false;
}

/**
 * add filter on shipping cart package on regular persian shipping methods
 */
add_filter('woap_shipping_package_data',function($shipping_data,$user_id){
    if(storina_pws_is_tapin_enabled()){
        return $shipping_data;
    }
	$taxonomy = 'state_city';
	$city = $shipping_data['shipping_city'];
	$city_term = get_term_by('name',$city,$taxonomy);
	$shipping_data['shipping_city'] = $city_term->term_id ?? $city;
	return $shipping_data;
},10,2);

/**
 * add filter on cart package based on tapin shipping method
 */
add_filter('woap_shipping_package_data',function($shipping_data,$user_id){
    if(!storina_pws_is_tapin_enabled()){
        return $shipping_data;
    }
    $city = $shipping_data['shipping_city'];
    $zone = PWS_Tapin::zone();
     foreach($zone as $state_id => $state_resource){
        $cities = $state_resource['cities'];
        $city_id = array_search($city,$cities);
        if($city_id){
            $shipping_data['shipping_city'] = $city_id;
        }
    }
    return $shipping_data;
},20,2);

/**
 * add filter on address fields
 */

add_filter('woap_index_address_fields',function($address_fields){
    if(!function_exists('PWS')){
		return $address_fields;
    }
    foreach($address_fields as &$fields){
        foreach($fields as &$field){
            if('billing_city' == $field['id'] || 'shipping_city' == $field['id']){
                $field['type'] = 'select';
            }
        }
    }
    return $address_fields;
});

/**
 * order address props action
 */

add_filter('woap_go_payment_address_props',function($order,$addresses,$type){
    if(!storina_pws_is_tapin_enabled()){
        return;
    }
    $zone = PWS_Tapin::zone();
    $chosen_address = $addresses[$type];
    $state_id = $chosen_address["state"];
    $state_name = PWS()::get_state($state_id);
    $city_name = $chosen_address['city'];
    $city_id = storina_pws_search_city_id($city_name,$zone);
    $order_id = $order->get_id();
    if(isset($state_id,$city_id)){
        update_post_meta($order_id,'_shipping_state',$state_name);
        update_post_meta($order_id,'_shipping_state_id',$state_id);
        update_post_meta($order_id,'_shipping_city',$city_name);
        update_post_meta($order_id,'_shipping_city_id',$city_id);
    }
},10,3);
