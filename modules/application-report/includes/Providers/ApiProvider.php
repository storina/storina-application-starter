<?php

namespace VOS\Providers;

class ApiProvider {

    public $api_mapper;
    public $service_provider;

    public function __construct($service_provider, $api_mapper) {
        $this->service_provider = $service_provider;
        $this->api_mapper = $api_mapper;
        add_action("template_redirect", array($this, "template_redirect"));
    }

    public function template_redirect() {
        $module = get_query_var("vos_module");
        if (empty($module)) {
            return;
        }
        $action = get_query_var("vos_action");
        $params = get_query_var("vos_params");
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        $object = $this->service_provider->get($this->api_mapper[$module]);
        if (!is_object($object)) {
            wp_send_json(['error' => "Class {$module} not exist"]);
            return;
        }
        if (!isset($action)) {
            $object->index();
            return;
        }
        $object->{$action}($params);
    }
    
}
