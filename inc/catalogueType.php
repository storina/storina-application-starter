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
add_action( 'plugins_loaded', function () {
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
});

/**
 * Add to product type drop down.
 */
add_filter( 'product_type_selector',function ( $types ){
   	$types[ 'simple_catalogue' ] = esc_html__("Catalogue",'onlinerShopApp');
	return $types;
});
