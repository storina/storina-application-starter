<?php
$info=array(
	'name' => 'aboutUS',
	'apppagename' => 'aboutUS-options',
	'title' => __('About US','onlinerShopApp'),
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
		"name" => __('Shop slogan','onlinerShopApp'),
		"id" => "app_slogan",
		"desc" => __('Enter the shop slogan','onlinerShopApp'),
	),
	array(
		"type" => "picture",
		"name" => __('select top page logo','onlinerShopApp'),
		"id" => "app_aboutlogo",
		"desc" => __('Select top page logo. PNG and width 700px height 445px','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => __('Email','onlinerShopApp'),
		"id" => "app_Email",
		"desc" =>__('Enter support Email','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => __('Telegram','onlinerShopApp'),
		"id" => "app_telegramID",
		"desc" => __('Enter the telegram ID (without @)','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => __('mubile number','onlinerShopApp'),
		"id" => "app_phone",
		"desc" => __('Enter support phone','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => __('copyright','onlinerShopApp'),
		"id" => "app_copyright",
		"desc" => __('Enter the copyright text. maximum 12 char','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => __('Privacy','onlinerShopApp'),
		"id" => "app_privacyLink",
		"desc" => __('Enter the privacy page url','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => __('Terms','onlinerShopApp'),
		"id" => "app_termsLink",
		"desc" => __('Enter the terms page url','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => __('About US','onlinerShopApp'),
		"id" => "app_aboutLink",
		"desc" => __('Enter the url of about us page','onlinerShopApp'),
	),
	array(
		"type" => "hr",
		"name" => __('Custom button','onlinerShopApp'),
		"id" => "",
		"desc" => "",
	),
	array(
		"type" => "text",
		"name" => __('button text','onlinerShopApp'),
		"id" => "app_aboutButtonText",
		"desc" => __('Enter the button text','onlinerShopApp'),
	),
	array(
		"type" => "text",
		"name" => __('URL button','onlinerShopApp'),
		"id" => "app_aboutButtonLink",
		"desc" => __('Enter the button url','onlinerShopApp'),
	),
);
global $options_page;
if($options_page){
array_push($options_page, $options);
}else{$options_page = Array ($options);}
?>