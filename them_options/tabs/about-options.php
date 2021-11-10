<?php
$info=array(
	'name' => 'aboutUS',
	'apppagename' => 'aboutUS-options',
	'title' => esc_html__('About US','storina-application'),
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
		"name" => esc_html__('Shop slogan','storina-application'),
		"id" => "app_slogan",
		"desc" => esc_html__('Enter the shop slogan','storina-application'),
	),
	array(
		"type" => "picture",
		"name" => esc_html__('select top page logo','storina-application'),
		"id" => "app_aboutlogo",
		"desc" => esc_html__('Select top page logo. PNG and width 700px height 445px','storina-application'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('Email','storina-application'),
		"id" => "app_Email",
		"desc" =>esc_html__('Enter support Email','storina-application'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('Telegram','storina-application'),
		"id" => "app_telegramID",
		"desc" => esc_html__('Enter the telegram ID (without @)','storina-application'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('mubile number','storina-application'),
		"id" => "app_phone",
		"desc" => esc_html__('Enter support phone','storina-application'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('copyright','storina-application'),
		"id" => "app_copyright",
		"desc" => esc_html__('Enter the copyright text. maximum 12 char','storina-application'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('Privacy','storina-application'),
		"id" => "app_privacyLink",
		"desc" => esc_html__('Enter the privacy page url','storina-application'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('Terms','storina-application'),
		"id" => "app_termsLink",
		"desc" => esc_html__('Enter the terms page url','storina-application'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('About US','storina-application'),
		"id" => "app_aboutLink",
		"desc" => esc_html__('Enter the url of about us page','storina-application'),
	),
	array(
		"type" => "hr",
		"name" => esc_html__('Custom button','storina-application'),
		"id" => "",
		"desc" => "",
	),
	array(
		"type" => "text",
		"name" => esc_html__('button text','storina-application'),
		"id" => "app_aboutButtonText",
		"desc" => esc_html__('Enter the button text','storina-application'),
	),
	array(
		"type" => "text",
		"name" => esc_html__('URL button','storina-application'),
		"id" => "app_aboutButtonLink",
		"desc" => esc_html__('Enter the button url','storina-application'),
	),
);
global $options_page;
if($options_page){
array_push($options_page, $options);
}else{$options_page = Array ($options);}
?>
