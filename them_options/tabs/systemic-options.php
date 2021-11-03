<?php
$info = array(
	"name"        => "system",
	"apppagename" => "system-options",
	"title"       => esc_html__( "systemic options", "onlinerShopApp" ),
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
		"name"         => esc_html__( "Caching mode", "onlinerShopApp" ),
		"id"           => "appCacheStatus",
		"options"      => array(
			"active"   => esc_html__( "Active", "onlinerShopApp" ),
			"inactive" => esc_html__( "Deactive", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "Caching mode increase your application load speed", "onlinerShopApp" ),
	),
	array(
		"type" => "text",
		"name" => esc_html__( "Application version", "onlinerShopApp" ),
		"id"   => "app_version",
		"desc" => esc_html__( "Enter application version", "onlinerShopApp" ),
	),
	array(
		"type" => "text",
		"name" => esc_html__( "English App Name", "onlinerShopApp" ),
		"id"   => "english_app_name",
		"desc" => esc_html__( "English App Name has the same name you enter when making apk file", "onlinerShopApp" ),
	),
	array(
		"type" => "picture",
		"name" => esc_html__( "APK file", "onlinerShopApp" ),
		"id"   => "app_url",
		"desc" => esc_html__( "Upload and select application file. *.apk", "onlinerShopApp" ),
	),
	array(
		"type" => "textarea",
		"name" => esc_html__( "new version description", "onlinerShopApp" ),
		"id"   => "app_versionText",
		"desc" => esc_html__( "Enter the new version descriptions. one item in one line.", "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__( "Fource update", "onlinerShopApp" ),
		"id"      => array( "app_UpdateFource" ),
		"options" => array( esc_html__( "Active", "onlinerShopApp" ) ),
		"desc"    => esc_html__( "If users must update to this version, active this field;", "onlinerShopApp" ),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "Archive level Start", "onlinerShopApp" ),
		"id"           => "appArchiveType",
		"options"      => array(
			"parent" => esc_html__( "Parent", "onlinerShopApp" ),
			"sub"    => esc_html__( "Sub category", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "when that user select a sub categories, show type be parent category or sam category?", "onlinerShopApp" ),
	),
	array(
		"type" => "text",
		"name" => esc_html__( "view count custom field", "onlinerShopApp" ),
		"id"   => "viewCounterField",
		"desc" => esc_html__( "Set your custom field for view counter", "onlinerShopApp" ),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "Payment type", "onlinerShopApp" ),
		"id"           => "payType",
		"options"      => array(
			"normal"   => esc_html__( "In browser", "onlinerShopApp" ),
			"inAppPay" => esc_html__( "In application", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "In (in application) method, when user go for payment , do not exit from application. ", "onlinerShopApp" ),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "Register type", "onlinerShopApp" ),
		"id"           => "registerType",
		"options"      => array(
			"email"  => esc_html__( "Only by email", "onlinerShopApp" ),
			"mobile" => esc_html__( "Only by mobile", "onlinerShopApp" ),
			"both"   => esc_html__( "by Email AND mobile", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "Select your registration type in application. need to digits plugin for mobile registration.", "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__( "Google map manifest", "onlinerShopApp" ),
		"id"      => array( "app_map_api" ),
		"options" => array( esc_html__( "Active", "onlinerShopApp" ) ),
		"desc"    => esc_html__( "google map on application and orders", "onlinerShopApp" ),
	),
    	array(
		"type"    => "checkbox",
		"name"    => esc_html__( "App send time field", "onlinerShopApp" ),
		"id"      => array( "app_send_time_field" ),
		"options" => array( esc_html__( "Active", "onlinerShopApp" ) ),
		"desc"    => esc_html__( "Application send time field activation", "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__( "Debug mode", "onlinerShopApp" ),
		"id"      => array( "debug_mode" ),
		"options" => array( esc_html__( "Active", "onlinerShopApp" ) ),
		"desc"    => esc_html__( "if you dont have any information about it, do not active it", "onlinerShopApp" ),
	),
	array(
		"type" => "text",
		"name" => esc_html__( "debug key", "onlinerShopApp" ),
		"id"   => "debug_key",
		"desc" => esc_html__( "Set your secret key for debug", "onlinerShopApp" ),
	),

);
if ( function_exists( "dokan_is_seller_enabled" ) ) {
	$options[] = array(
		"type"    => "checkbox",
		"name"    => esc_html__("vendor grouping","onlinerShopApp"),
		"id"      => array( "app_vendor_grouping" ),
		"options" => array( esc_html__( "Active", "onlinerShopApp" ) ),
		"desc"    => esc_html__("do you want activate state/city select box for vendors in app?","onlinerShopApp"),
	);
	$options[] =
	array(
		"type"         => "select",
		"name"         => esc_html__( "Vendor group by", "onlinerShopApp" ),
		"id"           => "appVendorlist",
		"options"      => array(
			"state" => esc_html__( "state", "onlinerShopApp" ),
			"city"  => esc_html__( "city", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "how to grouping vendors.", "onlinerShopApp" ),
	);
	$options[] = array(
		"type"    => "checkbox",
		"name"    => esc_html__("hidden empty states","onlinerShopApp"),
		"id"      => array( "app_hidden_empty_state" ),
		"options" => array( esc_html__( "Active", "onlinerShopApp" ) ),
		"desc"    => esc_html__("do you want hidden empty states in select box?","onlinerShopApp"),
	);
	$options[] = array(
		"type"    => "checkbox",
		"name"    => esc_html__("hide vendor list on menu","onlinerShopApp"),
		"id"      => array( "app_hidden_menu_vendor_list" ),
		"options" => array( esc_html__( "hidden", "onlinerShopApp" ) ),
		"desc"    => esc_html__("if you want to hide vendor list on menu active this","onlinerShopApp"),
	);
	$options[] = [
		'type' => 'select',
		'name' => esc_html__("Dokan shipping type","onlinerShopApp"),
		'id' => 'app_dokan_shipping_type',
		'options' => [
			'regular_shipping' => esc_html__("regular shipping","onlinerShopApp"),
			'vendor_shipping' => esc_html__("vendor shipping","onlinerShopApp")
		],
		'desc' => esc_html__("select shipping type. vendor shipping is having seprate shipping method for each seller","onlinerShopApp")
	];
}

$options = apply_filters("osa_theme_options_systemic", $options);

global $options_page;
if ( $options_page ) {
	array_push( $options_page, $options );
} else {
	$options_page = Array( $options );
}
