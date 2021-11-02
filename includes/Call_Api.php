<?php

namespace STORINA;

defined('ABSPATH') || exit;

use \STORINA\Controllers\General;
use \STORINA\Router;

class Call_Api {

    public $service_provider;
    public $router;
    public $general;

    public function __construct($service_provider) {
        $this->router = new Router();
        $this->service_provider = $service_provider;
        $this->general = $service_provider->get(General::class);
        add_action('generate_rewrite_rules', array($this, 'add_rewrite_rule'));
        add_action('query_vars', array($this, 'add_query_vars'));
        add_action('parse_request', array($this, 'parse_api_request'));
    }

    public function prepare_response($action, $log_id) {
        do_action("osa_init_response");
        if (!empty(sanitize_text_field($_POST['googleID']))) {
            global $googleID;
            $googleID = sanitize_text_field($_POST['googleID']);
        }
        $action = $this->general->checkAction($action);
        $module = $this->router->get($action);
        $object = $this->service_provider->get($module['class']);
        $method = $module['method'];
        $params = $module['params'];
        $result = call_user_func(array($object,$method), $params);
        if ($action AND $log_id > 0) {
            $this->set_error_log('update', '', $log_id, '', '', $result);
        }
        wp_send_json($result);
    }

    public function add_rewrite_rule($wp_rewrite) {
        $new_rules = array();
        $new_rules['^onlinerApi'] = 'index.php?onapi=true';
        $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
        if (!empty(sanitize_text_field(@$_POST['action']))) {
            set_query_var('onlinerApi', sanitize_text_field($_POST['action']));
        } else {
            $arr = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
            $index = array_search('onlinerApi', $arr);
            $action = (is_numeric($index) && !empty($arr[$index++])) ? $arr[$index++] : false;
            set_query_var('onlinerApi', $action);
        }
    }

    public function add_query_vars($query_vars) {
        $query_vars[] = 'onapi';
        return $query_vars;
    }

    function parse_api_request(&$wp) {
        $client_type = sanitize_text_field($_POST['client_type']) ?? 'ios';
		$request_type = $wp->query_vars['woap_request_type'];
		$action = sanitize_text_field($_POST['action']) ?? 'getVersion';
        if('android' != $client_type && $action != 'getVersion' && $request_type == 'ios'){
            return;
        }
        do_action("osa_init_request");
        if (!array_key_exists('onapi', $wp->query_vars)) {
            return;
        }
        header('Content-Type: application/json');
        $q_var = get_query_var('onlinerApi');
        if (false && !in_array('ionCube Loader', get_loaded_extensions())) {
            wp_send_json(array(
                'posts' => array(
                    'status' => false,
                    'data' => array(
                        'message' => __('ioncube loader +5.6 Not installed.', 'onlinerShopApp')
                    )
                ),
            ));
        }
        $action = ( !empty($_GET['getVersion'])) ? 'getVersion' : $q_var;
        //trigger exception in a "try" block
        try {
            if (!empty(sanitize_text_field($_POST['action']))) {
                $log_id = $this->set_error_log('insert', '', '', sanitize_text_field($_POST['action']), sanitize_text_field($_POST), '');
            }
            $this->prepare_response($action, $log_id);
        } catch (Throwable $e) {
            $errors = array(
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            );
            if ($action AND $log_id > 0) {
                $this->set_error_log('update', '', $log_id, '', '', json_encode($errors));
            }
            print_r($errors);
        }
        exit();
    }

    function set_error_log($action, $googleID, $id, $script, $post, $repond) {
        if ('true' != storina_get_option('debug_mode')) {
            return;
        }
        global $wpdb;
        $table = $wpdb->prefix . 'OSA_error_log';
        $date = date("Y-m-d H:i:s");
        $post_param = json_encode($post);
        if ($action == 'insert') {
            $result = $wpdb->insert(
                    $table,
                    array(
                        'googleID' => http_build_query($post),
                        'script' => $script,
                        'post' => $post_param,
                        'date' => $date,
                    ),
                    array(
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    )
            );
            return $wpdb->insert_id;
        } elseif ($action == 'update') {
            $result = $wpdb->update(
                    $table,
                    array('respond' => json_encode($repond)),
                    array('id' => $id)
            );
        }
    }

}
