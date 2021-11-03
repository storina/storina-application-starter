<?php

namespace CRN\Middlewares;

class Authentication {

    public $service_provider;
    
    public function __construct($service_provider){
        $this->service_provider = $service_provider;
        add_filter('crn_authentication_middleware',[$this,'authenticate_user_id'],10,2);
    }

    public function authenticate_user_id($authentication,$user_id){
        if(!is_numeric($user_id) || false == $user_id){
            return ([
                'status' => false,
                'message' => esc_html__('Authentication failed,user not found','crn')
            ]);
        }
	return ['status' => true];
    }

}
