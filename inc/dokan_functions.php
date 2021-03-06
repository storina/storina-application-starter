<?php
/**
 * Created by PhpStorm.
 * User: ali
 * Date: 8/30/18
 * Time: 1:22 PM
 */
/** Adding Settings extra menu in Settings tabs Dahsboard */
add_filter( 'dokan_get_dashboard_settings_nav',function ( $settings_tab ) {
	$settings_tab['OSA'] = array(
		'title' => esc_html__( 'آنلاینر اپ', 'dokan' ),
		'icon'  => '<i class="fa fa-mobile"></i>',
		'url'   => dokan_get_navigation_url( 'settings/OSA' ),
		'pos'   => 32
	);

	return $settings_tab;
});


add_filter( 'dokan_dashboard_settings_heading_title', function ( $header, $query_vars ) {
	if ( $query_vars == 'OSA' ) {
		$header = esc_html__( 'تنظیمات اپلیکیشن', 'dokan' );
	}
	return $header;
}, 11, 2 );

add_action( 'dokan_render_settings_content', function ( $query_vars ) {
	if ( isset( $query_vars['settings'] ) && $query_vars['settings'] == 'OSA' ) {
		$user = wp_get_current_user();
		if ( ! in_array( 'seller', (array) $user->roles ) ) {
			wp_die( esc_html__( 'شما مجوز ویرایش تنظیمات قالب را ندارید.' ) );
		}

		global $options_page, $pages;
		$options_page = $pages = array();

		include( __DIR__ . "/../vendor_options/create_list.php" );
		global $options_page;
		global $pages;

		$options_page = $pages = array();
		include( __DIR__ . "/../vendor_options/include.php" );
		include( __DIR__ . "/../vendor_options/on5-panel.php" );
		if ( ! is_admin() ) {
			vendor_update_options();
		}
		global $pages;
		$pages = array();
		require( __DIR__ . "/../vendor_options/include.php" );
		include( __DIR__ . "/../vendor_options/html-panel.php" );
	}
} , 10 );


/**
 * dokan vendor shipping implements
 * ----------------------------------------------------------
 * 1.woap_confirm_shipping_resources
 * 2.woap_get_cart_shipping_total_cost
 * 3.woap_gopayment_shipping_status
 * 4.woap_gopayment_vendor_shipping
 * ----------------------------------------------------------
 */

add_filters('woap_confirm_shipping_resources',function($shipping_resources,$cart){
	$shipping_type = storina_get_option('app_shipping_type') ?? 'regular_shipping';
	$dokan_activation = function_exists('dokan');
	if('regular_shipping' == $shipping_type || !$dokan_activation){
		return $shipping_resources;
	}
	foreach($cart as $key => $item){
		$product_id  = $item['product_id'];
		$seller_ids[] = get_post_field( 'post_author', $product_id );
	}
	$seller_ids = array_unique(array_map('intval',$seller_ids),SORT_NUMERIC);
	foreach($seller_ids as $seller_id){
		$vendor_shipping[$seller_id] = $shipping_resources;
	}
	return [
		'type' => 'vendor_shipping',
		'data' => $vendor_shipping
	];
},10,2);

add_filter('woap_get_cart_shipping_total_cost',function($shipping_cost,$active_shipping_methods,$cart_row){
	$shipping_type = storina_get_option('app_shipping_type') ?: 'regular_shipping';
	$dokan_activation = function_exists('dokan');
	$chosen_shipping_ids = json_decode($cart_row->shipping_method_id);
	if('vendor_shipping' == $shipping_type || !$dokan_activation || empty($chosen_shipping_ids)){
		return $shipping_cost;
	}
	foreach($chosen_shipping_ids as $seller_id => $shipping_id){
		$shipping_costs[] = (int) $active_shipping_methods[$shipping_id]['cost'] ?? 0;
	}
	return array_sum($shipping_costs);
},10,3);

add_filter('woap_get_shipping_methods_chosen_ids',function($chosen_method_ids,$active_shipping_methods,$cart_row){
	$shipping_type = storina_get_option('app_shipping_type') ?: 'regular_shipping';
	$dokan_activation = function_exists('dokan');
	$chosen_shipping_ids = json_decode($cart_row->shipping_method_id);
	if('vendor_shipping' == $shipping_type || !$dokan_activation || empty($chosen_shipping_ids)){
		return $chosen_method_ids;
	}
	$chosen_method_ids = [];
	foreach($chosen_shipping_ids as $seller_id => $method_id){
		$chosen_method_ids[] = $method_id;
	}
	return $chosen_method_ids;
},10,3);

add_filter('woap_gopayment_shipping_status',function($status){
	$shipping_type = storina_get_option('app_shipping_type') ?: 'regular_shipping';
	$dokan_activation = function_exists('dokan');
	return ('vendor_shipping' == $shipping_type && $dokan_activation)? false : $status;
});

add_action('woap_gopayment_vendor_shipping',function($shipping_rate_resources,$order){
	$shipping_type = storina_get_option('app_shipping_type') ?? 'regular_shipping';
	$dokan_activation = function_exists('dokan');
	if('regular_shipping' == $shipping_type || !$dokan_activation){
		return;
	}
	#get order childrens in dokan
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
	if(false == $order instanceof WC_Order || empty($order_childrens)){
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
		$wc_order_child->calculate_total();
		$wc_order_child->save();
	}
	#add shipping rate and meta data to order
	foreach($shipping_rate_resources as $seller_id => $shipping_rate_resource){
		$wc_shipping_rate = new WC_Shipping_Rate($shipping_rate_resource['id'], $shipping_rate_resource['title'], $shipping_rate_resource['cost']);
		$wc_shipping_rate->add_meta_data('seller_id',$seller_id);
		$order->add_shipping($wc_shipping_rate);
	}
	$order->calculate_total();
	$order->save();
},10,2);
