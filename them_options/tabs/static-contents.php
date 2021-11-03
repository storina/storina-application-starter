<?php
$info=array(
	'name' => 'staticContents',
	'apppagename' => 'static-contents',
	'title' => esc_html__('Static Pages',"onlinerShopApp"),
);


global $pages;
if($pages){
array_push($pages, $info);
}else{$pages = Array ($info);}
$options=array(
	array(
		"type" => "textarea",
		"name" => esc_html__( "Privacy And Policy", "onlinerShopApp" ),
		"id"   => "app_privacy_policy",
	),
	array(
		"type" => "textarea",
		"name" => esc_html__( "Terms And Conditions", "onlinerShopApp" ),
		"id"   => "‬app_terms_conditions",
	),
	array(
		"type" => "textarea",
		"name" => esc_html__( "Shopping Guildline", "onlinerShopApp" ),
		"id"   => "app_shopping_guide",
	),
);
global $options_page;
if($options_page){
array_push($options_page, $options);
}else{$options_page = Array ($options);}
?>
