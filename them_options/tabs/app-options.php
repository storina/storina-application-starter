<?php
$info=array(
	'name' => 'index',
	'apppagename' => 'index-options',
	'title' => esc_html__('General',"onlinerShopApp"),
);
global $pages;
if($pages){
array_push($pages, $info);
}else{$pages = Array ($info);}
$options=array(

	array(
		"type" => "text",
		"name" => esc_html__('application title',"onlinerShopApp"),
		"id" => "app_title",
		"desc" => esc_html__('Enter the application title. use in register page and login page and etc...',"onlinerShopApp"),
	),
	array(
		"type" => "picture",
		"name" => esc_html__('Splash screen logo',"onlinerShopApp"),
		"id" => "app_logo",
		esc_html__('Select the splash screen logo. PNG and 512px*512px',"onlinerShopApp")
		),
	array(
		"type" => "picture",
		"name" => esc_html__('Top page logo',"onlinerShopApp"),
		"id" => "app_TopLogo",
		esc_html__('Select the home page logo. this logo shows in top&left application home page. PNG witch 200px height 40px',"onlinerShopApp")
		),
	array(
		"type" => "color",
		"name" => esc_html__('Master application color',"onlinerShopApp"),
		"id" => "app_masterColor",
		"desc" => esc_html__('Please select main application color',"onlinerShopApp"),
	),
	array(
		"type" => "color",
		"name" => esc_html__('secondary application color',"onlinerShopApp"),
		"id" => "app_secondColor",
		"desc" => esc_html__('Please select secondary application color',"onlinerShopApp"),
	),
	array(
		"type" => "color",
		"name" => esc_html__("application icon color", "onlinerShopApp" ),
		"id"   => "app_IconColor",
		"desc" => esc_html__( 'Please select application icon color', "onlinerShopApp" ),
	),


	array(
		"type" => "listbox",
		"name" => esc_html__("Blog","onlinerShopApp"),
		"id" => "appBlog",
		"options" => $wp_cats,
		"return_value" => false,
		"desc" => esc_html__("Select categories that you want show their articles in application blog.","onlinerShopApp"),
	),
	array(
		"type" => "listbox",
		"name" => esc_html__("product categories menu","onlinerShopApp"),
		"id" => "appProCats",
		"options" => $product_cats,
		"return_value" => false,
		"desc" => esc_html__("Select parent categories for show in product categories menu. pay attention that selected categories must have sub category.","onlinerShopApp"),
	),

	array(
		"type" => "select",
		"name" => esc_html__("variation price show type","onlinerShopApp"),
		"id" => "variation_priceType",
		"options" => array(esc_html__("Single price","onlinerShopApp"), esc_html__("Period prices","onlinerShopApp")),
		"return_value" => false,
		"desc" => esc_html__("Can you select show type price in home page product boxes","onlinerShopApp"),
		"default" => esc_html__("Single price","onlinerShopApp"),
	),
	/*array(
		"type" => "select",
		"name" => esc_html__("Register type","onlinerShopApp"),
		"id" => "registerType",
		"options" => array('email' => esc_html__("Only by email","onlinerShopApp"),'both' => esc_html__("by Email OR sms","onlinerShopApp")),
		"return_value" => false,
		"desc" => esc_html__("Select your registration type in application","onlinerShopApp"),
	),*/
	array(
		"type" => "text",
		"name" => esc_html__( "Call number", "onlinerShopApp" ),
		"id"   => "app_callNumber",
		"desc" => esc_html__( "Enter the call number for call icon on top of home page.", "onlinerShopApp" ),
	),


	/*array(
		"type" => "text",
		"name" => esc_html__( "Google map api", "onlinerShopApp" ),
		"id"   => "app_map_api_code",
		"desc" => esc_html__( "Enter the Google map api for user addresses.", "onlinerShopApp" ),
	),*/
	array(
		"type"         => "select",
		"name"         => esc_html__( "Show vendor avatar", "onlinerShopApp" ),
		"id"           => "VendorAvatar",
		"options"      => array(
			'Hidden' => esc_html__( "Hidden", "onlinerShopApp" ),
			'Show'   => esc_html__( "Show", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "Show vendor avatar on product box in home page.", "onlinerShopApp" ),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "Blog menu", "onlinerShopApp" ),
		"id"           => "appblogsetting",
		"options"      => array(
			'Hidden' => esc_html__( "Hidden", "onlinerShopApp" ),
			'Show'   => esc_html__( "Show", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "Show Blog menu item in home page.", "onlinerShopApp" ),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "Shoping list", "onlinerShopApp" ),
		"id"           => "appShopinglist",
		"options"      => array(
			'Hidden' => esc_html__( "Hidden", "onlinerShopApp" ),
			'Show'   => esc_html__( "Show", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "Show Shoping list item in home page.", "onlinerShopApp" ),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "Show new menu", "onlinerShopApp" ),
		"id"           => "showNewMenu",
		"options"      => array(
			'Hidden' => esc_html__( "Hidden", "onlinerShopApp" ),
			'Show'   => esc_html__( "Show", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "Show new menu in home page.", "onlinerShopApp" ),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "compare system", "onlinerShopApp" ),
		"id"           => "appcompareactive",
		"options"      => array(
			'Active'   => esc_html__( "Active", "onlinerShopApp" ),
			'Inactive' => esc_html__( "Inactive", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => esc_html__( "activation of compare system in application.", "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__( "Send time field", "onlinerShopApp" ),
		"id"      => array( "app_send_time_field" ),
		"options" => array( esc_html__( "Active", "onlinerShopApp" ) ),
		"desc"    => esc_html__( "If users must set send time, active this field", "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__( "Show out of stock products at last", "onlinerShopApp" ),
		"id"      => array( "stock_out_order" ),
		"options" => array( esc_html__( "Active", "onlinerShopApp" ) ),
		"desc"    => esc_html__( "if user select sort based of stock status. out of stock product will shown at last", "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__("Validate phone number","onlinerShopApp"),
		"id"      => array( "app_verifyFource" ),
		"options" => array( esc_html__("required","onlinerShopApp") ),
		"desc"    => esc_html__("would you like to validate phone number of users in checkout fields?","onlinerShopApp"),
	),
	array(
		"type"         => "select",
		"name"         => "",
		"id"           => "app_loginVerifyType",
		"options"      => array(
			'password' => esc_html__( "Password", "onlinerShopApp" ),
			'sms'      => esc_html__( "sms", "onlinerShopApp" )
		),
		"return_value" => false,
		"desc"         => esc_html__("What's your verify type ? email or phone","onlinerShopApp")
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__("Show Full Name in register form","onlinerShopApp"),
		"id"      => array( "app_registerNameField" ),
		"options" => array( esc_html__('show', "onlinerShopApp")  ),
		"desc"    => esc_html__("Show Full Name field in register form","onlinerShopApp" )
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__("Show Vendors Phone","onlinerShopApp"),
		"id"      => array( "app_showVendorPhone" ),
		"options" => array( esc_html__('show', "onlinerShopApp")  ),
		"desc"    => esc_html__("Show vendors phone in information box in vendor store ", "onlinerShopApp" )
	),
	array(
		"type" => "text",
		"name" => esc_html__( "Backorder text", "onlinerShopApp" ),
		"id"   => "app_backorder_text",
		"desc" => esc_html__( "enter backorder button text", "onlinerShopApp" ),
	),
	array(
		"type" => "text",
		"name" => esc_html__( "Backorder email", "onlinerShopApp" ),
		"id"   => "app_backorder_email",
		"desc" => esc_html__( "enter email for backorder requests", "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__('Product English title', "onlinerShopApp" ),
		"id"      => array( "app_showProductNameEnglish" ),
		"options" => array(esc_html__('hide', "onlinerShopApp") ),
		"desc"    => esc_html__('Hide product english title in sigle product page', "onlinerShopApp" ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__("Force Login","onlinerShopApp"),
		"id"      => array( "app_ForceLogin" ),
		"options" => array(esc_html__("Force Login","onlinerShopApp")),
		"desc"    => esc_html__("Force user to login at startup","onlinerShopApp"),
	),

);


$options = apply_filters("osa_theme_options_app", $options);

global $options_page;
if($options_page){
array_push($options_page, $options);
}else{$options_page = Array ($options);}
