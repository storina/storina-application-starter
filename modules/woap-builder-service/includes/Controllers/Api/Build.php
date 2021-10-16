<?php

namespace WOAP\Controllers\Api;

use WOAP\Packages\Controller;


class Build extends Controller{
	
	public function updateDetails(){
		$request_token = $this->request->input('token');
		$validate_token = get_option('woap_build_request_token');
		if($request_token != $validate_token){
			return;
		}
		$options = ['woap_build_details_periority','woap_build_details_status','woap_build_created_at','woap_build_details_status','woap_build_build_attempt','woap_build_apk_url'];
		foreach($options as $option_name){
			$request_value = $this->request->input($option_name);
			if(!isset($request_value)){
				continue;
			}
			$option_value = $this->request->input($option_name);
			update_option($option_name,$option_value);
		}
	}
}
