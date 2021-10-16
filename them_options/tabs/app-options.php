<?php
$info=array(
	'name' => 'index',
	'apppagename' => 'index-options',
	'title' => __('General',"onlinerShopApp"),
);
global $pages;
if($pages){
array_push($pages, $info);
}else{$pages = Array ($info);}
$options=array(

	array(
		"type" => "text",
		"name" => __('application title',"onlinerShopApp"),
		"id" => "app_title",
		"desc" => __('Enter the application title. use in register page and login page and etc...',"onlinerShopApp"),
	),
	array(
		"type" => "picture",
		"name" => __('Splash screen logo',"onlinerShopApp"),
		"id" => "app_logo",
		__('Select the splash screen logo. PNG and 512px*512px',"onlinerShopApp")
		),
	array(
		"type" => "picture",
		"name" => __('Top page logo',"onlinerShopApp"),
		"id" => "app_TopLogo",
		__('Select the home page logo. this logo shows in top&left application home page. PNG witch 200px height 40px',"onlinerShopApp")
		),
	array(
		"type" => "color",
		"name" => __('Master application color',"onlinerShopApp"),
		"id" => "app_masterColor",
		"desc" => __('Please select main application color',"onlinerShopApp"),
	),
	array(
		"type" => "color",
		"name" => __('secondary application color',"onlinerShopApp"),
		"id" => "app_secondColor",
		"desc" => __('Please select secondary application color',"onlinerShopApp"),
	),
	array(
		"type" => "color",
		"name" => __("application icon color", "onlinerShopApp" ),
		"id"   => "app_IconColor",
		"desc" => __( 'Please select application icon color', "onlinerShopApp" ),
	),


	array(
		"type" => "listbox",
		"name" => __("Blog","onlinerShopApp"),
		"id" => "appBlog",
		"options" => $wp_cats,
		"return_value" => false,
		"desc" => __("Select categories that you want show their articles in application blog.","onlinerShopApp"),
	),
	array(
		"type" => "listbox",
		"name" => __("product categories menu","onlinerShopApp"),
		"id" => "appProCats",
		"options" => $product_cats,
		"return_value" => false,
		"desc" => __("Select parent categories for show in product categories menu. pay attention that selected categories must have sub category.","onlinerShopApp"),
	),

	array(
		"type" => "select",
		"name" => __("variation price show type","onlinerShopApp"),
		"id" => "variation_priceType",
		"options" => array(__("Single price","onlinerShopApp"), __("Period prices","onlinerShopApp")),
		"return_value" => false,
		"desc" => __("Can you select show type price in home page product boxes","onlinerShopApp"),
		"default" => __("Single price","onlinerShopApp"),
	),
	/*array(
		"type" => "select",
		"name" => __("Register type","onlinerShopApp"),
		"id" => "registerType",
		"options" => array('email' => __("Only by email","onlinerShopApp"),'both' => __("by Email OR sms","onlinerShopApp")),
		"return_value" => false,
		"desc" => __("Select your registration type in application","onlinerShopApp"),
	),*/
	array(
		"type" => "text",
		"name" => __( "Call number", "onlinerShopApp" ),
		"id"   => "app_callNumber",
		"desc" => __( "Enter the call number for call icon on top of home page.", "onlinerShopApp" ),
	),


	/*array(
		"type" => "text",
		"name" => __( "Google map api", "onlinerShopApp" ),
		"id"   => "app_map_api_code",
		"desc" => __( "Enter the Google map api for user addresses.", "onlinerShopApp" ),
	),*/
	array(
		"type"         => "select",
		"name"         => __( "Show vendor avatar", "onlinerShopApp" ),
		"id"           => "VendorAvatar",
		"options"      => array(
			'Hidden' => __( "Hidden", "onlinerShopApp" ),
			'Show'   => __( "Show", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => __( "Show vendor avatar on product box in home page.", "onlinerShopApp" ),
	),
	array(
		"type"         => "select",
		"name"         => __( "Blog menu", "onlinerShopApp" ),
		"id"           => "appblogsetting",
		"options"      => array(
			'Hidden' => __( "Hidden", "onlinerShopApp" ),
			'Show'   => __( "Show", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => __( "Show Blog menu item in home page.", "onlinerShopApp" ),
	),
	array(
		"type"         => "select",
		"name"         => __( "Shoping list", "onlinerShopApp" ),
		"id"           => "appShopinglist",
		"options"      => array(
			'Hidden' => __( "Hidden", "onlinerShopApp" ),
			'Show'   => __( "Show", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => __( "Show Shoping list item in home page.", "onlinerShopApp" ),
	),
	array(
		"type"         => "select",
		"name"         => __( "Show new menu", "onlinerShopApp" ),
		"id"           => "showNewMenu",
		"options"      => array(
			'Hidden' => __( "Hidden", "onlinerShopApp" ),
			'Show'   => __( "Show", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => __( "Show new menu in home page.", "onlinerShopApp" ),
	),
	array(
		"type"         => "select",
		"name"         => __( "compare system", "onlinerShopApp" ),
		"id"           => "appcompareactive",
		"options"      => array(
			'Active'   => __( "Active", "onlinerShopApp" ),
			'Inactive' => __( "Inactive", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => __( "activation of compare system in application.", "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => __( "Send time field", "onlinerShopApp" ),
		"id"      => array( "app_send_time_field" ),
		"options" => array( __( "Active", "onlinerShopApp" ) ),
		"desc"    => __( "If users must set send time, active this field", "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => __( "Show out of stock products at last", "onlinerShopApp" ),
		"id"      => array( "stock_out_order" ),
		"options" => array( __( "Active", "onlinerShopApp" ) ),
		"desc"    => __( "if user select sort based of stock status. out of stock product will shown at last", "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => __("Validate phone number","onlinerShopApp"),
		"id"      => array( "app_verifyFource" ),
		"options" => array( __("required","onlinerShopApp") ),
		"desc"    => __("would you like to validate phone number of users in checkout fields?","onlinerShopApp"),
	),
	array(
		"type"         => "select",
		"name"         => "",
		"id"           => "app_loginVerifyType",
		"options"      => array(
			'password' => __( "Password", "onlinerShopApp" ),
			'sms'      => __( "sms", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => __("What's your verify type ? email or phone","onlinerShopApp")
	),
	array(
		"type"    => "checkbox",
		"name"    => __("Show Full Name in register form","onlinerShopApp"),
		"id"      => array( "app_registerNameField" ),
		"options" => array( __('show', "onlinerShopApp")  ),
		"desc"    => __("Show Full Name field in register form","onlinerShopApp" )
	),
	array(
		"type"    => "checkbox",
		"name"    => __("Show Vendors Phone","onlinerShopApp"),
		"id"      => array( "app_showVendorPhone" ),
		"options" => array( __('show', "onlinerShopApp")  ),
		"desc"    => __("Show vendors phone in information box in vendor store ", "onlinerShopApp" )
	),
	array(
		"type" => "text",
		"name" => __( "Backorder text", "onlinerShopApp" ),
		"id"   => "app_backorder_text",
		"desc" => __( "enter backorder button text", "onlinerShopApp" ),
	),
	array(
		"type" => "text",
		"name" => __( "Backorder email", "onlinerShopApp" ),
		"id"   => "app_backorder_email",
		"desc" => __( "enter email for backorder requests", "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => __('Product English title', "onlinerShopApp" ),
		"id"      => array( "app_showProductNameEnglish" ),
		"options" => array(__('hide', "onlinerShopApp") ),
		"desc"    => __('Hide product english title in sigle product page', "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => __("Force Login","onlinerShopApp"),
		"id"      => array( "app_ForceLogin" ),
		"options" => array(__("Force Login","onlinerShopApp")),
		"desc"    => __("Force user to login at startup","onlinerShopApp"),
	),

);


$options = apply_filters("osa_theme_options_app", $options);

global $options_page;
if($options_page){
array_push($options_page, $options);
}else{$options_page = Array ($options);}
