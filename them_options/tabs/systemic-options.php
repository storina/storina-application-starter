<?php
$info = array(
	"name"        => "system",
	"apppagename" => "system-options",
	"title"       => esc_html__( "systemic options", "storina-application" ),
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
		"name"         => esc_html__( "Caching mode", "storina-application" ),
		"id"           => "appCacheStatus",
		"options"      => array(
			"active"   => esc_html__( "Active", "storina-application" ),
			"inactive" => esc_html__( "Deactive", "storina-application" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "Caching mode increase your application load speed", "storina-application" ),
	),
	array(
		"type" => "text",
		"name" => esc_html__( "Application version", "storina-application" ),
		"id"   => "app_version",
		"desc" => esc_html__( "Enter application version", "storina-application" ),
	),
	array(
		"type" => "text",
		"name" => esc_html__( "English App Name", "storina-application" ),
		"id"   => "english_app_name",
		"desc" => esc_html__( "English App Name has the same name you enter when making apk file", "storina-application" ),
	),
	array(
		"type" => "picture",
		"name" => esc_html__( "APK file", "storina-application" ),
		"id"   => "app_url",
		"desc" => esc_html__( "Upload and select application file. *.apk", "storina-application" ),
	),
	array(
		"type" => "textarea",
		"name" => esc_html__( "new version description", "storina-application" ),
		"id"   => "app_versionText",
		"desc" => esc_html__( "Enter the new version descriptions. one item in one line.", "storina-application" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__( "Fource update", "storina-application" ),
		"id"      => array( "app_UpdateFource" ),
		"options" => array( esc_html__( "Active", "storina-application" ) ),
		"desc"    => esc_html__( "If users must update to this version, active this field;", "storina-application" ),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "Archive level Start", "storina-application" ),
		"id"           => "appArchiveType",
		"options"      => array(
			"parent" => esc_html__( "Parent", "storina-application" ),
			"sub"    => esc_html__( "Sub category", "storina-application" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "when that user select a sub categories, show type be parent category or sam category?", "storina-application" ),
	),
	array(
		"type" => "text",
		"name" => esc_html__( "view count custom field", "storina-application" ),
		"id"   => "viewCounterField",
		"desc" => esc_html__( "Set your custom field for view counter", "storina-application" ),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "Payment type", "storina-application" ),
		"id"           => "payType",
		"options"      => array(
			"normal"   => esc_html__( "In browser", "storina-application" ),
			"inAppPay" => esc_html__( "In application", "storina-application" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "In (in application) method, when user go for payment , do not exit from application. ", "storina-application" ),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "Register type", "storina-application" ),
		"id"           => "registerType",
		"options"      => array(
			"email"  => esc_html__( "Only by email", "storina-application" ),
			"mobile" => esc_html__( "Only by mobile", "storina-application" ),
			"both"   => esc_html__( "by Email AND mobile", "storina-application" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "Select your registration type in application. need to digits plugin for mobile registration.", "storina-application" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__( "Google map manifest", "storina-application" ),
		"id"      => array( "app_map_api" ),
		"options" => array( esc_html__( "Active", "storina-application" ) ),
		"desc"    => esc_html__( "google map on application and orders", "storina-application" ),
	),
    	array(
		"type"    => "checkbox",
		"name"    => esc_html__( "App send time field", "storina-application" ),
		"id"      => array( "app_send_time_field" ),
		"options" => array( esc_html__( "Active", "storina-application" ) ),
		"desc"    => esc_html__( "Application send time field activation", "storina-application" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__( "Debug mode", "storina-application" ),
		"id"      => array( "debug_mode" ),
		"options" => array( esc_html__( "Active", "storina-application" ) ),
		"desc"    => esc_html__( "if you dont have any information about it, do not active it", "storina-application" ),
	),
	array(
		"type" => "text",
		"name" => esc_html__( "debug key", "storina-application" ),
		"id"   => "debug_key",
		"desc" => esc_html__( "Set your secret key for debug", "storina-application" ),
	),

);
if ( function_exists( "dokan_is_seller_enabled" ) ) {
	$options[] = array(
		"type"    => "checkbox",
		"name"    => esc_html__("vendor grouping","storina-application"),
		"id"      => array( "app_vendor_grouping" ),
		"options" => array( esc_html__( "Active", "storina-application" ) ),
		"desc"    => esc_html__("do you want activate state/city select box for vendors in app?","storina-application"),
	);
	$options[] =
	array(
		"type"         => "select",
		"name"         => esc_html__( "Vendor group by", "storina-application" ),
		"id"           => "appVendorlist",
		"options"      => array(
			"state" => esc_html__( "state", "storina-application" ),
			"city"  => esc_html__( "city", "storina-application" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "how to grouping vendors.", "storina-application" ),
	);
	$options[] = array(
		"type"    => "checkbox",
		"name"    => esc_html__("hidden empty states","storina-application"),
		"id"      => array( "app_hidden_empty_state" ),
		"options" => array( esc_html__( "Active", "storina-application" ) ),
		"desc"    => esc_html__("do you want hidden empty states in select box?","storina-application"),
	);
	$options[] = array(
		"type"    => "checkbox",
		"name"    => esc_html__("hide vendor list on menu","storina-application"),
		"id"      => array( "app_hidden_menu_vendor_list" ),
		"options" => array( esc_html__( "hidden", "storina-application" ) ),
		"desc"    => esc_html__("if you want to hide vendor list on menu active this","storina-application"),
	);
	$options[] = [
		'type' => 'select',
		'name' => esc_html__("Dokan shipping type","storina-application"),
		'id' => 'app_dokan_shipping_type',
		'options' => [
			'regular_shipping' => esc_html__("regular shipping","storina-application"),
			'vendor_shipping' => esc_html__("vendor shipping","storina-application")
		],
		'desc' => esc_html__("select shipping type. vendor shipping is having seprate shipping method for each seller","storina-application")
	];
}

$options = apply_filters("osa_theme_options_systemic", $options);

global $options_page;
if ( $options_page ) {
	array_push( $options_page, $options );
} else {
	$options_page = Array( $options );
}
