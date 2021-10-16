<?php
$info = array(
	"name"        => "system",
	"apppagename" => "system-options",
	"title"       => __( "systemic options", "onlinerShopApp" ),
);
global $pages;
if ( $pages ) {
	array_push( $pages, $info );
} else {
	$pages = Array( $info );
}
$options = array(
	array(
		"type"         => "select",
		"name"         => __( "Caching mode", "onlinerShopApp" ),
		"id"           => "appCacheStatus",
		"options"      => array(
			"active"   => __( "Active", "onlinerShopApp" ),
			"inactive" => __( "Deactive", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => __( "Caching mode increase your application load speed", "onlinerShopApp" ),
	),
	array(
		"type" => "text",
		"name" => __( "Application version", "onlinerShopApp" ),
		"id"   => "app_version",
		"desc" => __( "Enter application version", "onlinerShopApp" ),
	),
	array(
		"type" => "text",
		"name" => __( "English App Name", "onlinerShopApp" ),
		"id"   => "english_app_name",
		"desc" => __( "English App Name has the same name you enter when making apk file", "onlinerShopApp" ),
	),
	array(
		"type" => "picture",
		"name" => __( "APK file", "onlinerShopApp" ),
		"id"   => "app_url",
		"desc" => __( "Upload and select application file. *.apk", "onlinerShopApp" ),
	),
	array(
		"type" => "textarea",
		"name" => __( "new version description", "onlinerShopApp" ),
		"id"   => "app_versionText",
		"desc" => __( "Enter the new version descriptions. one item in one line.", "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => __( "Fource update", "onlinerShopApp" ),
		"id"      => array( "app_UpdateFource" ),
		"options" => array( __( "Active", "onlinerShopApp" ) ),
		"desc"    => __( "If users must update to this version, active this field;", "onlinerShopApp" ),
	),
	array(
		"type"         => "select",
		"name"         => __( "Archive level Start", "onlinerShopApp" ),
		"id"           => "appArchiveType",
		"options"      => array(
			"parent" => __( "Parent", "onlinerShopApp" ),
			"sub"    => __( "Sub category", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => __( "when that user select a sub categories, show type be parent category or sam category?", "onlinerShopApp" ),
	),
	array(
		"type" => "text",
		"name" => __( "view count custom field", "onlinerShopApp" ),
		"id"   => "viewCounterField",
		"desc" => __( "Set your custom field for view counter", "onlinerShopApp" ),
	),
	array(
		"type"         => "select",
		"name"         => __( "Payment type", "onlinerShopApp" ),
		"id"           => "payType",
		"options"      => array(
			"normal"   => __( "In browser", "onlinerShopApp" ),
			"inAppPay" => __( "In application", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => __( "In (in application) method, when user go for payment , do not exit from application. ", "onlinerShopApp" ),
	),
	array(
		"type"         => "select",
		"name"         => __( "Register type", "onlinerShopApp" ),
		"id"           => "registerType",
		"options"      => array(
			"email"  => __( "Only by email", "onlinerShopApp" ),
			"mobile" => __( "Only by mobile", "onlinerShopApp" ),
			"both"   => __( "by Email AND mobile", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => __( "Select your registration type in application. need to digits plugin for mobile registration.", "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => __( "Google map manifest", "onlinerShopApp" ),
		"id"      => array( "app_map_api" ),
		"options" => array( __( "Active", "onlinerShopApp" ) ),
		"desc"    => __( "google map on application and orders", "onlinerShopApp" ),
	),
    	array(
		"type"    => "checkbox",
		"name"    => __( "App send time field", "onlinerShopApp" ),
		"id"      => array( "app_send_time_field" ),
		"options" => array( __( "Active", "onlinerShopApp" ) ),
		"desc"    => __( "Application send time field activation", "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => __( "Debug mode", "onlinerShopApp" ),
		"id"      => array( "debug_mode" ),
		"options" => array( __( "Active", "onlinerShopApp" ) ),
		"desc"    => __( "if you dont have any information about it, do not active it", "onlinerShopApp" ),
	),
	array(
		"type" => "text",
		"name" => __( "debug key", "onlinerShopApp" ),
		"id"   => "debug_key",
		"desc" => __( "Set your secret key for debug", "onlinerShopApp" ),
	),

);
if ( function_exists( "dokan_is_seller_enabled" ) ) {
	$options[] = array(
		"type"    => "checkbox",
		"name"    => __("vendor grouping","onlinerShopApp"),
		"id"      => array( "app_vendor_grouping" ),
		"options" => array( __( "Active", "onlinerShopApp" ) ),
		"desc"    => __("do you want activate state/city select box for vendors in app?","onlinerShopApp"),
	);
	$options[] =
	array(
		"type"         => "select",
		"name"         => __( "Vendor group by", "onlinerShopApp" ),
		"id"           => "appVendorlist",
		"options"      => array(
			"state" => __( "state", "onlinerShopApp" ),
			"city"  => __( "city", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => __( "how to grouping vendors.", "onlinerShopApp" ),
	);
	$options[] = array(
		"type"    => "checkbox",
		"name"    => __("hidden empty states","onlinerShopApp"),
		"id"      => array( "app_hidden_empty_state" ),
		"options" => array( __( "Active", "onlinerShopApp" ) ),
		"desc"    => __("do you want hidden empty states in select box?","onlinerShopApp"),
	);
	$options[] = array(
		"type"    => "checkbox",
		"name"    => __("hide vendor list on menu","onlinerShopApp"),
		"id"      => array( "app_hidden_menu_vendor_list" ),
		"options" => array( __( "hidden", "onlinerShopApp" ) ),
		"desc"    => __("if you want to hide vendor list on menu active this","onlinerShopApp"),
	);
	$options[] = [
		'type' => 'select',
		'name' => __("Dokan shipping type","onlinerShopApp"),
		'id' => 'app_dokan_shipping_type',
		'options' => [
			'regular_shipping' => __("regular shipping","onlinerShopApp"),
			'vendor_shipping' => __("vendor shipping","onlinerShopApp")
		],
		'desc' => __("select shipping type. vendor shipping is having seprate shipping method for each seller","onlinerShopApp")
	];
}

$options = apply_filters("osa_theme_options_systemic", $options);

global $options_page;
if ( $options_page ) {
	array_push( $options_page, $options );
} else {
	$options_page = Array( $options );
}
