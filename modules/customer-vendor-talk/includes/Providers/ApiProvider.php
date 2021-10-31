<?php

namespace CRN\Providers;

class ApiProvider {

    public $api_mapper;
    public $service_provider;

    public function __construct($service_provider, $api_mapper) {
        $this->service_provider = $service_provider;
        $this->api_mapper = $api_mapper;
        add_action("template_redirect", array($this, "template_redirect"));
		add_filter('woap_crn_response_body',[$this,'set_request_log'],10,3);
    }

	public function set_request_log($response,$module,$action){
		$debug_mode_status = get_option('debug_mode');
		if('true' != $debug_mode_status){
			return $response;
		}
		$prefix = 'crn';
		$time = time();
		$option_name = "{$prefix}-{$time}";
		$request_body=http_build_query(sanitize_text_field($_POST));
		$request_rule = "{$module}/{$action}";
		$option_value = [
			'request_body' => $request_body,
			'response' => $response,
			'request_rule' => $request_rule, 
		];
		update_option($option_name,$option_value);
		return $response;
	}

    public function template_redirect() {
        $module = get_query_var("crn_module");
        if (empty($module)) {
            return;
        }
        $action = get_query_var("crn_action");
        $params = get_query_var("crn_params");
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        $object = $this->service_provider->get($this->api_mapper[$module]);
        if (!is_object($object)) {
            return (['error' => "Class {$module} not exist"]);
            return;
        }
        if (!isset($action)) {
            $object->index();
            return;
        }
        $response = $object->{$action}($params);
	wp_send_json(apply_filters('woap_crn_response_body',$response,$module,$action));
    }
    
}
