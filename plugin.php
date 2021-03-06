<?php

/*
  Plugin Name: Storina Application Starter
  Plugin URI: https://storina.com
  Description: The REST API works on a person shopping application. It compatible with any woocommerce websites.
  Version: 1.2.0
  Author: storina
  Text Domain: storina-application
  License: A "Slug" license name e.g. GPL2
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly


class storina_application_init {

    public function __construct() {
        $this->define_constant();
        $this->construct_debug();
        $this->boot_modules();
        add_action("init", array($this, "plugin_init"));
        $this->boot_services();
        register_activation_hook(__FILE__, array($this, "register_activation_hook"));
        register_deactivation_hook(__FILE__, array($this, "register_deactivation_hook"));
        add_action('plugins_loaded',[$this,'database_migration']);
    }

    public function database_migration(){
        $option_key = 'woap_db_migration_1604176541';
        $option_value = get_option($option_key) ?? 0;
        $migration_status = filter_var($option_value,FILTER_VALIDATE_BOOLEAN);
        if($migration_status){
            return;
        }
        osa_create_tables();
        update_option($option_key,1);
    }

	public function define_constant(){
        define("STORINA_FILE", __FILE__);
        define("STORINA_PDU", plugin_dir_url(__FILE__));
        define("STORINA_PDP", plugin_dir_path(__FILE__));
        define('STORINA_PLUGIN_PATH', plugin_dir_path(__FILE__));
        define('STORINA_STORAGE', plugin_dir_path(__FILE__) . "/assets/storage/");
        define('STORINA_PLUGIN_URL', plugin_dir_url(__FILE__));	
		define('STORINA_THEME_OPTION', trailingslashit(STORINA_PLUGIN_URL).'them_options/');
	}

    public function construct_debug() {
		$debug_mode = get_option('debug_mode');
		if('true' == $debug_mode){
			@ini_set('log_errors','On');
			@ini_set('display_errors','Off');
			@ini_set('error_log',__DIR__ . '/debug.log');
		}	
    }

    public function boot_modules() {
        $requires = array(
            "includes/helpers.php",
            "includes/Libraries/jdate.php",
            "inc/woocommerce.php",
            "inc/persian-woocommerce-shipping.php",
            "inc/location-query-filter.php",
            "inc/dokan-multivendor-module.php",
            "inc/dokan-functions.php",
            "inc/woocommerce-bulk-discount.php",
            "includes/ModulesController.php",
        );
        foreach ($requires as $require) {
            require_once trailingslashit(__DIR__) . $require;
        }
        do_action("onlinershopapp_modules_loaded");
    }

    public function boot_services(){
        $requires = array(
            #Api
            "includes/Service_Provider.php",
            "includes/Router.php",
            "includes/Call_Api.php",
			"includes/Autoload.php",
            #Web
            "them_options/on5_functions.php",
            "them_options/on5-panel.php",
            "inc/user_field.php",
            "inc/order_functions.php",
            "inc/woocommerce_custom_field.php",
			"inc/catalogueType.php",
        );
        foreach($requires as $require){
            require_once trailingslashit(__DIR__) . $require;
        }
        do_action("onlinershopapp_loaded");
    }

    public function register_activation_hook() {
        storina_delete_all_cache();
        storina_create_tables();
    }

    public function register_deactivation_hook() {
        storina_delete_all_cache();
    }

    public function plugin_init() {
        load_plugin_textdomain('storina-application', false, basename(dirname(__FILE__)) . '/languages');
        $woocommerce = (class_exists("woocommerce")) ? "active" : "deactive";
        $onliner_shop_app = (function_exists("storina_get_option")) ? "active" : "deactive";
    }

}

new storina_application_init();
