<?php
/**
 * dokan vendor shipping implements
 * ----------------------------------------------------------
 * 1.woap_confirm_shipping_resources
 * 2.woap_get_cart_shipping_total_cost
 * 3.woap_gopayment_shipping_status
 * 4.woap_gopayment_vendor_shipping
 * ----------------------------------------------------------
 */

add_filter('woap_confirm_shipping_method_resources',function($shipping_resources,$cart){
    $shipping_type = osa_get_option('app_dokan_shipping_type') ?: 'regular_shipping';
    $dokan_activation = function_exists('dokan');
    $client_type = $_POST['client_type'] ?? 'ios';
    if('regular_shipping' == $shipping_type || !$dokan_activation || 'ios' == $client_type){
        return $shipping_resources;
    }
    foreach($cart as $key => $item){
        $product_id  = $item['product_id'];
        $seller_ids[] = get_post_field( 'post_author', $product_id );
    }
    $seller_ids = array_unique(array_map('intval',$seller_ids),SORT_NUMERIC);
    foreach($seller_ids as $seller_id){
        $shipping_data[] = [
            'seller_id' => $seller_id,
            'rates' => $shipping_resources
        ];
    }
    return [
        'type' => 'vendor_shipping',
        'data' => array_values($shipping_data)
    ];
},10,2);

add_filter('woap_get_cart_shipping_total_cost',function($shipping_cost,$active_shipping_methods,$cart_row){
    $shipping_type = osa_get_option('app_dokan_shipping_type') ?: 'regular_shipping';
    $dokan_activation = function_exists('dokan');
    $chosen_shipping_ids = json_decode(stripslashes($cart_row->shipping_method_id));
    $client_type = $_POST['client_type'] ?? 'ios';
    if('regular_shipping' == $shipping_type || !$dokan_activation || empty($chosen_shipping_ids) || 'ios' == $client_type){
        return $shipping_cost;
    }
    foreach($chosen_shipping_ids as $seller_id => $shipping_id){
        $shipping_costs[] = (int) $active_shipping_methods[$shipping_id]['cost'] ?? 0;
    }
    return array_sum($shipping_costs);
},10,3);

add_filter('woap_get_shipping_methods_chosen_ids',function($chosen_method_ids,$active_shipping_methods,$cart_row){
    $shipping_type = osa_get_option('app_dokan_shipping_type') ?: 'regular_shipping';
    $dokan_activation = function_exists('dokan');
    $chosen_shipping_ids = json_decode(stripslashes($cart_row->shipping_method_id));
    $client_type = $_POST['client_type'] ?? 'ios';
    if('regular_shipping' == $shipping_type || !$dokan_activation || empty($chosen_shipping_ids) || 'ios' == $client_type){
        return $chosen_method_ids;
    }
    $chosen_method_ids = [];
    foreach($chosen_shipping_ids as $seller_id => $method_id){
        $chosen_method_ids[$seller_id] = $method_id;
    }
    return $chosen_method_ids;
},10,3);

add_filter('woap_gopayment_shipping_status',function($status){
    $shipping_type = osa_get_option('app_dokan_shipping_type') ?: 'regular_shipping';
    $dokan_activation = function_exists('dokan');
    $client_type = $_POST['client_type'] ?? 'ios';
    return ('vendor_shipping' == $shipping_type && $dokan_activation && 'android' == $client_type)? false : $status;
});

add_action('woap_gopayment_vendor_shipping',function($shipping_rate_resources,$order){
    $shipping_type = osa_get_option('app_dokan_shipping_type') ?: 'regular_shipping';
    $dokan_activation = function_exists('dokan');
    $client_type = $_POST['client_type'] ?? 'ios';
    if('regular_shipping' == $shipping_type || !$dokan_activation || 'ios' == $client_type){
        return;
    }
    #get order childrens in dokan
    $order_id = $order->get_id();
    $order_childrens = get_children(
        [
            'post_parent' => $order_id,
            'post_type'   => 'shop_order',
            'post_status' => [
                    'wc-pending',
                    'wc-completed',
                    'wc-processing',
                    'wc-on-hold',
                    'wc-cancelled',
            ],
        ]
    );
    if(false == $order instanceof WC_Order){
        return;
    }
    #add shipping rate and meta data to order childrens
    foreach($order_childrens as $wp_order_child){
        $order_child_id = $wp_order_child->ID;
        $seller_id = dokan_get_seller_id_by_order($order_child_id);
        $shipping_rate_resource = $shipping_rate_resources[$seller_id];
        $wc_shipping_rate = new WC_Shipping_Rate($shipping_rate_resource['id'], $shipping_rate_resource['title'], $shipping_rate_resource['cost']);
        $wc_shipping_rate->add_meta_data('seller_id',$seller_id);
        $wc_order_child = wc_get_order($order_child_id);
        $wc_order_child->add_shipping($wc_shipping_rate);
        $wc_order_child->calculate_totals();
        $wc_order_child->save();
    }
    #add shipping rate and meta data to order
    foreach($shipping_rate_resources as $seller_id => $shipping_rate_resource){
        $wc_shipping_rate = new WC_Shipping_Rate($shipping_rate_resource['id'], $shipping_rate_resource['title'], $shipping_rate_resource['cost']);
        $wc_shipping_rate->add_meta_data('seller_id',$seller_id);
        $order->add_shipping($wc_shipping_rate);
    }
    $order->calculate_totals();
    $order->save();
},10,2);

add_action('woocommerce_order_status_changed',function($order_id,$from,$to){
		$order_id=($order instanceof WC_Order)? $order->get_id() : $order_id;
		$purchase_type=get_post_meta($order_id,'purchase_type',true);
		if('app' != $purchase_type && 'complated' == $to){
				return;
		}
		$order=wc_get_order($order_id);	

		$has_sub_order = wp_get_post_parent_id( $order->get_id() );

		// seems it's not a parent order so return early
		if ( ! $has_sub_order ) {
				return;
		}

		// Loop over all items.
		foreach ( $order->get_items() as $item ) {
				if ( ! $item->is_type( 'line_item' ) ) {
						continue;
				}

				$item->delete_meta_data( '_reduced_stock' );
				$item->save();
		}
},10,3);
