<?php

namespace CRN\Controllers\Web;

class HomeController {
	
	public function __construct(){
		add_action('osa_index_get_app_info',[$this,'dokan_activation_status']);
	}

	public function dokan_activation_status($app_info){
		$app_info['dokan_activation_status']= (function_exists('dokan'))? true : false;
		return $app_info;
	}
}
