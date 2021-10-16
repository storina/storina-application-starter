<?php
/**
 * Created by PhpStorm.
 * User: ali akherati
 * Date: 5/23/2018
 * Time: 4:23 PM
 */
/**
 * Register the custom product type after init
 */
function register_simple_catalogue_product_type() {
	/**
	 * This should be in its own separate file.
	 */
	if(!function_exists('WC')){
		return;
	}
	class WC_Product_Simple_Catalogue extends WC_Product {
		public function __construct( $product ) {
			$this->product_type = 'simple_catalogue';
			parent::__construct( $product );
		}
	}
}
add_action( 'plugins_loaded', 'register_simple_catalogue_product_type' );
/**
 * Add to product type drop down.
 */
function add_simple_catalogue_product( $types ){
	// Key should be exactly the same as in the class
	$types[ 'simple_catalogue' ] = __("Catalogue",'onlinerShopApp');
	return $types;
}
add_filter( 'product_type_selector', 'add_simple_catalogue_product' );
