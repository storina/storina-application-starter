<?php
$info = array(
	'name'        => 'single',
	'apppagename' => 'single-options',
	'title'       => esc_html__( 'single options', 'onlinerShopApp' ),
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
		"name" => esc_html__( "products count in archive page", 'onlinerShopApp' ),
		"id"   => "Archive_product_count",
		"desc" => esc_html__( "Enter the products count in archive page", 'onlinerShopApp' ),
	),
	array(
		"type" => "text",
		"name" => esc_html__( "Call text", 'onlinerShopApp' ),
		"id"   => "zeroPriceText",
		"desc" => esc_html__( "In home page, som products have zero price. for they show (call) text. if you want to change it please enter the text that you want", 'onlinerShopApp' ),
	),
	array(
		"type"    => "checkbox",
		"name"    => esc_html__("Hide Out Of Stock Product","onlinerShopApp"),
		"id"      => array( "app_disableExist" ),
		"options" => array( esc_html__("Hide","onlinerShopApp") ),
		"desc"    => esc_html__('Hide out of stock product form archive and search query','onlinerShopApp'),
	),
	array(
		"type"         => "select",
		"name"         => esc_html__( "related product by", 'onlinerShopApp' ),
		"id"           => "related_product_by",
		"options"      => array( esc_html__( "Woocommerce default", 'onlinerShopApp' ), esc_html__( "category", 'onlinerShopApp' ) ),
		"return_value" => false,
		"desc"         => esc_html__( "show related product by which case?", 'onlinerShopApp' ),
		"default"      => esc_html__( "category", 'onlinerShopApp' ),
	),
);


global $options_page;
if ( $options_page ) {
	array_push( $options_page, $options );
} else {
	$options_page = Array( $options );
}
