<?php

namespace WOAP\Packages;

class Router {

	public $service_container;
	public $route_mapping;
	
	public function __construct($service_container,$route_mapping){
		$this->service_container = $service_container;
		$this->route_mapping = $route_mapping;
        add_action("init", array($this, "add_rewrite_rule"));
		add_action('template_redirect', [$this,'route_init']);
	}

    public function add_rewrite_rule() {
		foreach($this->route_mapping as $slug => $route_item){
			add_rewrite_rule("^{$slug}/([^/]*)/?([^/]*)/?([^/]*)/?$", 'index.php?woap_class=$matches[1]&woap_action=$matches[2]&woap_params=$matches[3]&woap_endpoint=' . $slug , "top");
			add_rewrite_tag("%woap_class%", "([^/]*)");
			add_rewrite_tag("%woap_action%", "([^/]*)");
			add_rewrite_tag("%woap_params%", "([^/]*)");
			add_rewrite_tag("%woap_endpoint%", "([^/]*)");
		}
    }

	public function route_init(){
        $endpoint = get_query_var("woap_endpoint");
		$namespace = $this->route_mapping[$endpoint]['namespace'];
		if(!isset($namespace,$endpoint)){
			return;
		}
		$type = $this->route_mapping[$endpoint]['type'] ?? 'api';
        $class = get_query_var("woap_class");
        $action = get_query_var("woap_action");
        $params = get_query_var("woap_params");
		$endpoint_hook=strtolower($endpoint);
		do_action("woap_authentication_{$endpoint_hook}_{$class}");
		do_action("woap_authentication_{$endpoint_hook}_{$class}_{$action}");
		do_action("woap_http_post_endpoint");
		do_action("woap_http_post_endpoint_{$endpoint_hook}");
		$namespace_class = "{$namespace}\\{$class}";
		$object = $this->service_container->get($namespace_class);
		$method_name = $type . "_response_body";
		$this->$method_name($object,$action,$params);
	}

	public function api_response_body($object,$action,$param){
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
		if(!is_object($object) || !method_exists($object,$action)){
			http_response_code(404);
			echo json_encode([
					'status' => false,
					'message' => '404 Not Found',
				]);		
			die();	
		}	
		$result = $object->$action($param);
		wp_send_json($result);
	}

	public function web_response_body($object,$action,$param){
		if(!is_object($object) || !method_exists($object,$action)){
			http_response_code(404);
			die('404 Not Found');
		}
		$object->$action($param);
	}

}
