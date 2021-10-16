<?php
$info = array(
	'name'        => 'single',
	'apppagename' => 'single-options',
	'title'       => __( 'single options', 'onlinerShopApp' ),
);
global $pages;
if ( $pages ) {
	array_push( $pages, $info );
} else {
	$pages = Array( $info );
}
$options = array(
	array(
		"type" => "text",
		"name" => __( "products count in archive page", 'onlinerShopApp' ),
		"id"   => "Archive_product_count",
		"desc" => __( "Enter the products count in archive page", 'onlinerShopApp' ),
	),
	array(
		"type" => "text",
		"name" => __( "Call text", 'onlinerShopApp' ),
		"id"   => "zeroPriceText",
		"desc" => __( "In home page, som products have zero price. for they show (call) text. if you want to change it please enter the text that you want", 'onlinerShopApp' ),
	),
	array(
		"type"    => "checkbox",
		"name"    => __("Hide Out Of Stock Product","onlinerShopApp"),
		"id"      => array( "app_disableExist" ),
		"options" => array( __("Hide","onlinerShopApp") ),
		"desc"    => __('Hide out of stock product form archive and search query','onlinerShopApp'),
	),
	array(
		"type"         => "select",
		"name"         => __( "related product by", 'onlinerShopApp' ),
		"id"           => "related_product_by",
		"options"      => array( __( "Woocommerce default", 'onlinerShopApp' ), __( "category", 'onlinerShopApp' ) ),
		"return_value" => false,
		"desc"         => __( "show related product by which case?", 'onlinerShopApp' ),
		"default"      => __( "category", 'onlinerShopApp' ),
	),
);


global $options_page;
if ( $options_page ) {
	array_push( $options_page, $options );
} else {
	$options_page = Array( $options );
}
