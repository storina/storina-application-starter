<?php
$info=array(
	'name' => 'index',
	'apppagename' => 'index-options',
	'title' => esc_html__('General',"storina-application"),
);
global $pages;
if($pages){
array_push($pages, $info);
}else{$pages = Array ($info);}
$options=array(

	array(
		"type" => "text",
		"name" => esc_html__('application title',"storina-application"),
		"id" => "app_title",
		"desc" => esc_html__('Enter the application title. use in register page and login page and etc...',"storina-application"),
	),
	array(
		"type" => "picture",
		"name" => esc_html__('Splash screen logo',"storina-application"),
		"id" => "app_logo",
		esc_html__('Select the splash screen logo. PNG and 512px*512px',"storina-application")
		),
	array(
		"type" => "picture",
		"name" => esc_html__('Top page logo',"storina-application"),
		"id" => "app_TopLogo",
		esc_html__('Select the home page logo. this logo shows in top&left application home page. PNG witch 200px height 40px',"storina-application")
		),
	array(
		"type" => "color",
		"name" => esc_html__('Master application color',"storina-application"),
		"id" => "app_masterColor",
		"desc" => esc_html__('Please select main application color',"storina-application"),
	),
	array(
		"type" => "color",
		"name" => esc_html__('secondary application color',"storina-application"),
		"id" => "app_secondColor",
		"desc" => esc_html__('Please select secondary application color',"storina-application"),
	),
	array(
		"type" => "color",
		"name" => esc_html__("application icon color", "storina-application" ),
		"id"   => "app_IconColor",
		"desc" => esc_html__( 'Please select application icon color', "storina-application" ),
	),


	array(
		"type" => "listbox",
		"name" => esc_html__("Blog","storina-application"),
		"id" => "appBlog",
		"options" => $wp_cats,
		"return_value" => false,
		"desc" => esc_html__("Select categories that you want show their articles in application blog.","storina-application"),
	),
	array(
		"type" => "listbox",
		"name" => esc_html__("product categories menu","storina-application"),
		"id" => "appProCats",
		"options" => $product_cats,
		"return_value" => false,
		"desc" => esc_html__("Select parent categories for show in product categories menu. pay attention that selected categories must have sub category.","storina-application"),
	),

	array(
		"type" => "select",
		"name" => esc_html__("variation price show type","storina-application"),
		"id" => "variation_priceType",
		"options" => array(esc_html__("Single price","storina-application"), esc_html__("Period prices","storina-application")),
		"return_value" => false,
		"desc" => esc_html__("Can you select show type price in home page product boxes","storina-application"),
		"default" => esc_html__("Single price","storina-application"),
	),
	/*array(
		"type" => "select",
		"name" => esc_html__("Register type","storina-application"),
		"id" => "registerType",
		"options" => array('email' => esc_html__("Only by email","storina-application"),'both' => esc_html__("by Email OR sms","storina-application")),
		"return_value" => false,
		"desc" => esc_html__("Select your registration type in application","storina-application"),
	),*/
	array(
		"type" => "text",
		"name" => esc_html__( "Call number", "storina-application" ),
		"id"   => "app_callNumber",
		"desc" => esc_html__( "Enter the call number for call icon on top of home page.", "storina-application" ),
	),


	/*array(
		"type" => "text",
		"name" => esc_html__( "Google map api", "storina-application" ),
		"id"   => "app_map_api_code",
		"desc" => esc_html__( "Enter the Google map api for user addresses.", "storina-application" ),
	),*/
	array(
		"type"         => "select",
		"name"         => esc_html__( "Show vendor avatar", "storina-application" ),
		"id"           => "VendorAvatar",
		"options"      => array(
			'Hidden' => esc_html__( "Hidden", "storina-application" ),
			'Show'   => esc_html__( "Show", "storina-application" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "Show vendor avatar on product box in home page.", "storina-application" ),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "Blog menu", "storina-application" ),
		"id"           => "appblogsetting",
		"options"      => array(
			'Hidden' => esc_html__( "Hidden", "storina-application" ),
			'Show'   => esc_html__( "Show", "storina-application" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "Show Blog menu item in home page.", "storina-application" ),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "Shoping list", "storina-application" ),
		"id"           => "appShopinglist",
		"options"      => array(
			'Hidden' => esc_html__( "Hidden", "storina-application" ),
			'Show'   => esc_html__( "Show", "storina-application" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "Show Shoping list item in home page.", "storina-application" ),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "Show new menu", "storina-application" ),
		"id"           => "showNewMenu",
		"options"      => array(
			'Hidden' => esc_html__( "Hidden", "storina-application" ),
			'Show'   => esc_html__( "Show", "storina-application" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "Show new menu in home page.", "storina-application" ),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "compare system", "storina-application" ),
		"id"           => "appcompareactive",
		"options"      => array(
			'Active'   => esc_html__( "Active", "storina-application" ),
			'Inactive' => esc_html__( "Inactive", "storina-application" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "activation of compare system in application.", "storina-application" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__( "Send time field", "storina-application" ),
		"id"      => array( "app_send_time_field" ),
		"options" => array( esc_html__( "Active", "storina-application" ) ),
		"desc"    => esc_html__( "If users must set send time, active this field", "storina-application" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__( "Show out of stock products at last", "storina-application" ),
		"id"      => array( "stock_out_order" ),
		"options" => array( esc_html__( "Active", "storina-application" ) ),
		"desc"    => esc_html__( "if user select sort based of stock status. out of stock product will shown at last", "storina-application" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__("Validate phone number","storina-application"),
		"id"      => array( "app_verifyFource" ),
		"options" => array( esc_html__("required","storina-application") ),
		"desc"    => esc_html__("would you like to validate phone number of users in checkout fields?","storina-application"),
	),
	array(
		"type"         => "select",
		"name"         => "",
		"id"           => "app_loginVerifyType",
		"options"      => array(
			'password' => esc_html__( "Password", "storina-application" ),
			'sms'      => esc_html__( "sms", "storina-application" )
		),
		"return_value" => false,
		"desc"         => esc_html__("What's your verify type ? email or phone","storina-application")
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__("Show Full Name in register form","storina-application"),
		"id"      => array( "app_registerNameField" ),
		"options" => array( esc_html__('show', "storina-application")  ),
		"desc"    => esc_html__("Show Full Name field in register form","storina-application" )
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__("Show Vendors Phone","storina-application"),
		"id"      => array( "app_showVendorPhone" ),
		"options" => array( esc_html__('show', "storina-application")  ),
		"desc"    => esc_html__("Show vendors phone in information box in vendor store ", "storina-application" )
	),
	array(
		"type" => "text",
		"name" => esc_html__( "Backorder text", "storina-application" ),
		"id"   => "app_backorder_text",
		"desc" => esc_html__( "enter backorder button text", "storina-application" ),
	),
	array(
		"type" => "text",
		"name" => esc_html__( "Backorder email", "storina-application" ),
		"id"   => "app_backorder_email",
		"desc" => esc_html__( "enter email for backorder requests", "storina-application" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__('Product English title', "storina-application" ),
		"id"      => array( "app_showProductNameEnglish" ),
		"options" => array(esc_html__('hide', "storina-application") ),
		"desc"    => esc_html__('Hide product english title in sigle product page', "storina-application" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__("Force Login","storina-application"),
		"id"      => array( "app_ForceLogin" ),
		"options" => array(esc_html__("Force Login","storina-application")),
		"desc"    => esc_html__("Force user to login at startup","storina-application"),
	),

);


$options = apply_filters("osa_theme_options_app", $options);

global $options_page;
if($options_page){
array_push($options_page, $options);
}else{$options_page = Array ($options);}
