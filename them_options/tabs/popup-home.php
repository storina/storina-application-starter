<?php
$info=array(
	'name' => 'popupHome',
	'apppagename' => 'popup-home',
	'title' => esc_html__('Pop up',"storina-application"),
);


global $pages;
if($pages){
array_push($pages, $info);
}else{$pages = Array ($info);}
global $osa_autoload;
$general = $osa_autoload->service_provider->get(\STORINA\Controllers\General::class);
$types = $general->clickEventList();
$options=array(
    array(
        'type' => 'checkbox',
        'name' => esc_html__("Popup Activation","storina-application"),
        'id' => ['app_popup_activation']
    ),
	array(
		"type" => "picture",
		"name" => esc_html__( "Image", "storina-application" ),
		"id"   => "app_popup_image",
    ),
	array(
		"type" => "text",
		"name" => esc_html__( "Title", "storina-application" ),
		"id"   => "app_popup_title",
	),
	array(
		"type" => "textarea",
		"name" => esc_html__( "Description", "storina-application" ),
		"id"   => "‬app_popup_body",
	),
    array(
        'type' => 'text',
        'name' => esc_html__("Link Text","storina-application"),
        'id' => 'app_popup_link_text'
    ),
    array(
        'type' => 'select',
        'name' => esc_html__("Link type","storina-application"),
        'id' => 'app_popup_link_type',
        'options' => $types
    ),
    array(
        'type' => 'text',
        'name' => esc_html__("Link Value","storina-application"),
        'id' => 'app_popup_link_value',
    ),
);
global $options_page;
if($options_page){
array_push($options_page, $options);
}else{$options_page = Array ($options);}
?>
