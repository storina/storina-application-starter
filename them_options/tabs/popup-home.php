<?php
$info=array(
	'name' => 'popupHome',
	'apppagename' => 'popup-home',
	'title' => __('Pop up',"onlinerShopApp"),
);


global $pages;
if($pages){
array_push($pages, $info);
}else{$pages = Array ($info);}
global $osa_autoload;
$general = $osa_autoload->service_provider->get('OSA_general');
$types = $general->clickEventList();
$options=array(
    array(
        'type' => 'checkbox',
        'name' => __("Popup Activation","onlinerShopApp"),
        'id' => ['app_popup_activation']
    ),
	array(
		"type" => "picture",
		"name" => __( "Image", "onlinerShopApp" ),
		"id"   => "app_popup_image",
    ),
	array(
		"type" => "text",
		"name" => __( "Title", "onlinerShopApp" ),
		"id"   => "app_popup_title",
	),
	array(
		"type" => "textarea",
		"name" => __( "Description", "onlinerShopApp" ),
		"id"   => "‬app_popup_body",
	),
    array(
        'type' => 'text',
        'name' => __("Link Text","onlinerShopApp"),
        'id' => 'app_popup_link_text'
    ),
    array(
        'type' => 'select',
        'name' => __("Link type","onlinerShopApp"),
        'id' => 'app_popup_link_type',
        'options' => $types
    ),
    array(
        'type' => 'text',
        'name' => __("Link Value","onlinerShopApp"),
        'id' => 'app_popup_link_value',
    ),
);
global $options_page;
if($options_page){
array_push($options_page, $options);
}else{$options_page = Array ($options);}
?>