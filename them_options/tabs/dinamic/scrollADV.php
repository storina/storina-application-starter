<?php
/**
 * Created by PhpStorm.
 * User: T-bag
 * Date: 2017/10/14
 * Time: 10:17 AM
 */
$info = array(
	'name'        => 'custom',
	'apppagename' => 'appSImages-options' . $elementID[ $i ],
	'title'       => __( "Scroll Banners ads", 'onlinerShopApp' ) . '<span style="display: none;"> #' . $elementID[ $i ] . '</span>',
);
global $pages;
if ( $pages ) {
	array_push( $pages, $info );
} else {
	$pages = Array( $info );
}
//سلول type همیشه در اولین خانه آرابه یاشد
$options = array(
	'type'               => 'custom',
	'title'              => __( "Select the banners for show in home page", 'onlinerShopApp' ),
	'custom_option_name' => 'custom_option',
	'custom_option2'     => 'Sbanner_customOption1' . $elementID[ $i ],
	'custom_option3'     => 'Sbanner_banner' . $elementID[ $i ],
	'custom_option4'     => 'Sbanner_linkType' . $elementID[ $i ],
	'custom_option5'     => 'Sbanner_linkValue' . $elementID[ $i ],
	'custom_option6'     => 'Sbanner_customOption5' . $elementID[ $i ],
	'custom_option7'     => 'Sbanner_customOption6' . $elementID[ $i ],
	'custom_option8'     => 'Sbanner_customOption7' . $elementID[ $i ],
	'custom_option9'     => 'Sbanner_customOption8' . $elementID[ $i ]
);
global $options_page;
if ( $options_page ) {
	array_push( $options_page, $options );
} else {
	$options_page = Array( $options );
}