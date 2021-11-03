<?php
$info=array(
	'name' => 'aboutUS',
	'apppagename' => 'aboutUS-options',
	'title' => esc_html__('About US','onlinerShopApp'),
);

/*
timeline categories
*/
$categories = get_categories('taxonomy=timeline_category&hide_empty=0&orderby=name');
$timeline_cats = array();
$timeline_cats[0] = "---";
foreach ($categories as $category_list ) {
       $timeline_cats[$category_list->cat_ID] = $category_list->cat_name;
}

global $pages;
if($pages){
array_push($pages, $info);
}else{$pages = Array ($info);}
$options=array(
	array(
		"type" => "text",
		"name" => esc_html__('Shop slogan','onlinerShopApp'),
		"id" => "app_slogan",
		"desc" => esc_html__('Enter the shop slogan','onlinerShopApp'),
	),
	array(
		"type" => "picture",
		"name" => esc_html__('select top page logo','onlinerShopApp'),
		"id" => "app_aboutlogo",
		"desc" => esc_html__('Select top page logo. PNG and width 700px height 445px','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('Email','onlinerShopApp'),
		"id" => "app_Email",
		"desc" =>esc_html__('Enter support Email','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('Telegram','onlinerShopApp'),
		"id" => "app_telegramID",
		"desc" => esc_html__('Enter the telegram ID (without @)','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('mubile number','onlinerShopApp'),
		"id" => "app_phone",
		"desc" => esc_html__('Enter support phone','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('copyright','onlinerShopApp'),
		"id" => "app_copyright",
		"desc" => esc_html__('Enter the copyright text. maximum 12 char','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('Privacy','onlinerShopApp'),
		"id" => "app_privacyLink",
		"desc" => esc_html__('Enter the privacy page url','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('Terms','onlinerShopApp'),
		"id" => "app_termsLink",
		"desc" => esc_html__('Enter the terms page url','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('About US','onlinerShopApp'),
		"id" => "app_aboutLink",
		"desc" => esc_html__('Enter the url of about us page','onlinerShopApp'),
	),
	array(
		"type" => "hr",
		"name" => esc_html__('Custom button','onlinerShopApp'),
		"id" => "",
		"desc" => "",
	),
	array(
		"type" => "text",
		"name" => esc_html__('button text','onlinerShopApp'),
		"id" => "app_aboutButtonText",
		"desc" => esc_html__('Enter the button text','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('URL button','onlinerShopApp'),
		"id" => "app_aboutButtonLink",
		"desc" => esc_html__('Enter the button url','onlinerShopApp'),
	),
);
global $options_page;
if($options_page){
array_push($options_page, $options);
}else{$options_page = Array ($options);}
?>
