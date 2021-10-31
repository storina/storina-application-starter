<?php

add_action( 'add_meta_boxes', function () {
	global $post;
	$order_id     = $post->ID;
	$address_type = get_post_meta( $order_id, 'address_type', true );
	$user_id      = get_post_meta( $order_id, '_customer_user', true );
	$billing_lat  = get_user_meta( $user_id, $address_type . '_lat', true );
	$billing_lng  = get_user_meta( $user_id, $address_type . '_lng', true );
	$api_key      = storina_get_option( 'app_map_api_code' );
	if ( $billing_lat AND $billing_lng AND $address_type ) {
		add_meta_box(
			'address_on_map',
			'آدرس روی نقشه',
			'storina_app_show_map',
			'shop_order',
			'normal',
			'high' );
	}
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	// See WP docs.
	if ( 'post.php' !== $hook ) {
		return;
	}
	//$api_key = storina_get_option( 'app_map_api_code' );
	wp_enqueue_script( 'app_google_map', 'https://maps.google.com/maps/api/js?key=' . $api_key . '&language=fa' );
	$domain_name = $_SERVER['HTTP_HOST'];
	$host        = explode( '.', $domain_name );
	$host        = array_reverse( $host );
	if ( $host[0] == 'ir' ) {
		wp_enqueue_script( 'app_custom', STORINA_PLUGIN_URL . 'assets/js/custom.js' );
	}

} );

function storina_app_show_map() {
	global $post;
	$order_id     = $post->ID;
	$address_type = get_post_meta( $order_id, 'address_type', true );
	if ( ! $address_type ) {
		$address_type = 'billing';
	}
	$user_id     = get_post_meta( $order_id, '_customer_user', true );
	$billing_lat = get_user_meta( $user_id, $address_type . '_lat', true );
	$billing_lng = get_user_meta( $user_id, $address_type . '_lng', true );
	?>
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" />
	<script type="text/javascript" src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js"></script>
	<div id="map" style="height: 300px"></div>
	<script>
		var map = L.map('map').setView([<?= $billing_lat ?>, <?= $billing_lng ?>], 17);

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		}).addTo(map);
		L.marker([<?= $billing_lat ?>, <?= $billing_lng ?>]).addTo(map)
			.openPopup();
	</script>
<?php
}

add_action( 'woocommerce_admin_order_data_after_shipping_address', function ( $order ) {
	if ( ! class_exists( 'WC_Checkout_Field_Editor' ) ) {
		return;
	}
	$order_id     = $order->get_id();
	$address_type = get_post_meta( $order_id, 'address_type', true );
	if ( $address_type == 'billing' ) {
		return;
	}
	$fields = array();
	$fields = array_merge( WC_Checkout_Field_Editor::get_fields( 'shipping' ), WC_Checkout_Field_Editor::get_fields( 'additional' ) );
	if ( is_array( $fields ) && ! empty( $fields ) ) {
		$fields_html = '';
		// Loop through all custom fields to see if it should be added
		foreach ( $fields as $name => $options ) {
			$enabled         = ( isset( $options['enabled'] ) && $options['enabled'] == false ) ? false : true;
			$is_custom_field = ( isset( $options['custom'] ) && $options['custom'] == true ) ? true : false;

			if ( isset( $options['show_in_order'] ) && $options['show_in_order'] && $enabled && $is_custom_field ) {
				$value = get_post_meta( $order_id, $name, true );
				if ( ! empty( $value ) ) {
					$label = isset( $options['label'] ) && ! empty( $options['label'] ) ? __( $options['label'], 'woocommerce' ) : $name;

					if ( is_account_page() ) {
						if ( apply_filters( 'thwcfd_view_order_customer_details_table_view', true ) ) {
							$fields_html .= '<tr><th>' . esc_attr( $label ) . ':</th><td>' . wptexturize( $value ) . '</td></tr>';
						} else {
							$fields_html .= '<br/><dt>' . esc_attr( $label ) . ':</dt><dd>' . wptexturize( $value ) . '</dd>';
						}
					} else {
						if ( apply_filters( 'thwcfd_thankyou_customer_details_table_view', true ) ) {
							$fields_html .= '<tr><th>' . esc_attr( $label ) . ':</th><td>' . wptexturize( $value ) . '</td></tr>';
						} else {
							$fields_html .= '<br/><dt>' . esc_attr( $label ) . ':</dt><dd>' . wptexturize( $value ) . '</dd>';
						}
					}
				}
			}
		}

		if ( $fields_html ) {
			do_action( 'thwcfd_order_details_before_custom_fields_table', $order );
			?>
            <table class="woocommerce-table woocommerce-table--custom-fields shop_table custom-fields">
				<?php
				echo $fields_html;
				?>
            </table>
			<?php
			do_action( 'thwcfd_order_details_after_custom_fields_table', $order );
		}
	};


} , 20, 1 );

add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
	if ( ! class_exists( 'WC_Checkout_Field_Editor' ) ) {
		return;
	}
	$order_id = $order->get_id();
	//var_dump( get_post_meta( $order_id ) );
	$fields   = array();
	$fields   = array_merge( WC_Checkout_Field_Editor::get_fields( 'billing' ), WC_Checkout_Field_Editor::get_fields( 'additional' ) );

	if ( is_array( $fields ) && ! empty( $fields ) ) {
		$fields_html = '';
		// Loop through all custom fields to see if it should be added
		foreach ( $fields as $name => $options ) {
			$enabled         = ( isset( $options['enabled'] ) && $options['enabled'] == false ) ? false : true;
			$is_custom_field = ( isset( $options['custom'] ) && $options['custom'] == true ) ? true : false;

			if ( isset( $options['show_in_order'] ) && $options['show_in_order'] && $enabled && $is_custom_field ) {
				$value = get_post_meta( $order_id, $name, true );
				if ( ! empty( $value ) ) {
					$label = isset( $options['label'] ) && ! empty( $options['label'] ) ? __( $options['label'], 'woocommerce' ) : $name;

					if ( is_account_page() ) {
						if ( apply_filters( 'thwcfd_view_order_customer_details_table_view', true ) ) {
							$fields_html .= '<tr><th>' . esc_attr( $label ) . ':</th><td>' . wptexturize( $value ) . '</td></tr>';
						} else {
							$fields_html .= '<br/><dt>' . esc_attr( $label ) . ':</dt><dd>' . wptexturize( $value ) . '</dd>';
						}
					} else {
						if ( apply_filters( 'thwcfd_thankyou_customer_details_table_view', true ) ) {
							$fields_html .= '<tr><th>' . esc_attr( $label ) . ':</th><td>' . wptexturize( $value ) . '</td></tr>';
						} else {
							$fields_html .= '<br/><dt>' . esc_attr( $label ) . ':</dt><dd>' . wptexturize( $value ) . '</dd>';
						}
					}
				}
			}
		}

		if ( $fields_html ) {
			do_action( 'thwcfd_order_details_before_custom_fields_table', $order );
			?>
            <table class="woocommerce-table woocommerce-table--custom-fields shop_table custom-fields">
				<?php
				echo $fields_html;
				?>
            </table>
			<?php
			do_action( 'thwcfd_order_details_after_custom_fields_table', $order );
		}
	};


} , 20, 1 );

add_filter( 'manage_edit-shop_order_columns', function ( $columns ) {

	//remove column
	unset( $columns['tags'] );

	//add column
	$columns['chanel'] = __( 'From', 'onlinerShopApp' );

	return $columns;
} , 15 );

add_action( 'manage_shop_order_posts_custom_column', function ( $column, $postid ) {
	if ( $column == 'chanel' ) {
		$purchase_type = get_post_meta( $postid, 'purchase_type', true );
		echo '<strong style="color: darkred;">' . ( ( $purchase_type == 'app' ) ? __( 'App', 'onlinerShopApp' ) : 'Website' ) . '</strong>';
	}
} , 10, 2 );


add_filter( 'manage_edit-shop_order_columns', function ( $columns ) {

	$new_columns = array();

	foreach ( $columns as $column_name => $column_info ) {

		$new_columns[ $column_name ] = $column_info;

		if ( 'order_date' === $column_name ) {
			$new_columns['chanel'] = __( 'From', 'onlinerShopApp' );
		}
	}

	return $new_columns;
} , 20 );


add_action( 'admin_print_styles', function () {
	$css = '.widefat .column-order_date,.widefat .manage-column.column-chanel { width: 5%; }';
	wp_add_inline_style( 'woocommerce_admin_styles', $css );
} );


add_action('woocommerce_thankyou','storina_update_terawallet');
add_action("woocommerce_payment_complete","storina_update_terawallet");
add_action("woocommerce_order_status_completed","storina_update_terawallet");

function storina_update_terawallet($order_id){
    if(!function_exists("woo_wallet")){
        return;
    }
    global $osa_autoload;
    $terawallet          = $osa_autoload->service_provider->get(\STORINA\Controllers\Terawallet::class);
    $wallet_applyed_ballance = get_post_meta($order_id, \STORINA\Controllers\Terawallet::meta_key,true);
    if(!$terawallet->check_wallet_key($wallet_applyed_ballance)){
        return;
    }
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();
    $params = array(
        'id' => $user_id,
        'type' => "debit",
        'amount' => $wallet_applyed_ballance,
        'detail' => "کسر مبلغ خرید از کیف پول بابت سفارش با شناسه {$order_id}",
    );
    $transaction_id = $terawallet->wallet_ballance($params);
    if(empty($transaction_id) or !isset($transaction_id)){
        $timestamp = time();
        error_log("wallet for user with id {$order->get_user_id()} not implement for order with id {$order_id} in {$timestamp} no transaction_id");
        return;
    }
	#do_action( 'woo_wallet_payment_processed', $order_id, $transaction_id);
    delete_post_meta($order_id, \STORINA\Controllers\Terawallet::meta_key);
}

add_filter("osa_index_get_app_info",function($app_info){
	$faq_shortcode_id = storina_get_option('app_faq_shortcode_id');
	$app_info['faqHidden'] = (!class_exists('SP_EASY_ACCORDION_FREE') || empty($faq_shortcode_id))? true : false;
	return $app_info;
},10,1);

add_filter('osa_theme_options_app',function($options){
	if(!class_exists('SP_EASY_ACCORDION_FREE')){
		return $options;
	}
	$options[] = 	array(
		"type" => "text",
		"name" => __( "FAQ", 'onlinerShopApp' ),
		"id"   => "app_faq_shortcode_id",
		"desc" => __( "enter faq shortcode id. <a href='https://nimb.ws/bOogTf' target='_blank'>Screenshot</a>", 'onlinerShopApp' ),
	);
	return $options;
});


add_action("osa_init_response", function () {
	$user_token = (!empty(sanitize_text_field($_POST['userToken'])))? sanitize_text_field($_POST['userToken']) : null;
	$notif_token = (!empty(sanitize_text_field($_POST['notifToken'])))? sanitize_text_field($_POST['notifToken']) : null;
	if(!isset($user_token,$notif_token)){
		return;
	}
	global $osa_autoload;
	$user_action = $osa_autoload->service_provider->get(\STORINA\Controllers\User::class);
	$user_id = $user_action->get_userID_byToken($user_token);
	if (!is_numeric($user_id)) {
		return;
	}
	update_user_meta($user_id, 'notifToken', $notif_token);
} );

add_filter('posts_clauses', function ($posts_clauses) {
	global $wpdb;
	$action = sanitize_text_field($_POST['action']) ?? null;
	$level = sanitize_text_field($_POST['level']) ?? null;
	$settings = storina_get_option('stock_out_order') ?? false;
	$action_structure = ('archive' == $action && '3' == $level);
    if ($settings && $action_structure) {
        $posts_clauses['join'] .= " INNER JOIN $wpdb->postmeta istockstatus ON ($wpdb->posts.ID = istockstatus.post_id) ";
        $posts_clauses['orderby'] = " istockstatus.meta_value ASC, " . $posts_clauses['orderby'];
        $posts_clauses['where'] = " AND istockstatus.meta_key = '_stock_status' AND istockstatus.meta_value <> '' " . $posts_clauses['where'];
    }
    return $posts_clauses;
} );

add_filter('woap_add_to_cart_validation',function($validation,$item,$cart){
	$product_id = $item['product_id'];
	$maximum_quantity = get_post_meta($product_id,'_advanced-qty-max',true) ?: 999999999;
	$cart_key = $item['product_id'] . $item['variation_id'];
	$cart_item = $cart[$cart_key] ?? null;
	$cart_quantity = $cart_item['quantity'] ?? 0;
	$request_quantity = $cart_quantity + $item['quantity'];
	$status = $maximum_quantity >= $request_quantity;
	if(!$status){
		$validation['status'] = false;
		$validation['messages'][] = __("Request Quantity is bigger than request quantity","onlinerShopApp");
		return $validation;
	}
	return $validation;
},10,3);

add_filter('woocommerce_cancel_unpaid_order',function($validation,$order){
	$created_via = $order->get_created_via();
	return ('application' == $created_via)? true : $validation;
},10,2);
