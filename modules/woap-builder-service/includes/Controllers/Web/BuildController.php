<?php

namespace WOAP\Controllers\Web;

use WOAP\Packages\Controller;
use WOAP\Controllers\Logic\BuildLogic;
use WOAP\Controllers\Logic\HttpRequest;

class BuildController extends Controller {

	const action='request_queue_add';
	const url = 'https://builder.onlinerapp.ir/woap-builder-queue';
	const endpoint = 'WOAPApi';
    
	public function __construct($service_container){
		parent::__construct($service_container);
		add_action("osa_admin_menu_application_main", array($this, "builder_wrapper_panel"), 20);
		add_action('woap_options_configuration_build_details_content',[$this,'build_details_content_html']);
		add_action('woap_options_configuration_build_request_content',[$this,'build_request_content_html']);
		add_action('admin_enqueue_scripts',[$this,'builder_enqueue_scripts']);
		add_action('wp_ajax_' . self::action , [$this,'request_queue_add_resposne']);
	}

	public function builder_wrapper_panel(){
        add_submenu_page('ONLINER_options', esc_html__('Build Panel', 'storina-application'), esc_html__('Build Panel', 'storina-application'), 'manage_options', 'build-panel', [$this,'build_panel_wrapper_html']);
	}

	public function build_panel_wrapper_html(){
		$active_section_value = sanitize_text_field($_GET['section'] ?? 'details');
		$sections = apply_filters('woap_options_configuration_build_sections',[
			'details' => esc_html__('Build Details','storina-application'),
			'request' => esc_html__('Build Request','storina-application'),
		]);
		require_once trailingslashit(WOAB_ADM) . 'build/build-wrapper.php';
	}

	public function builder_enqueue_scripts($hook){
		$page = sanitize_text_field($_GET['page'] ?? null);
		$section = sanitize_text_field($_GET['section'] ?? null);	
		#implement logic for enqueue scripts
		if(!isset($section)){
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style('woap-builder-style',trailingslashit(WOAB_PDU) . 'admin/assets/css/build.css',['wp-color-picker']);
		wp_enqueue_script('woap-builder-script',trailingslashit(WOAB_PDU) . 'admin/assets/js/build.js',['jquery','wp-color-picker']);
		$data = [
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'action' => [
				'queueAdd' => self::action
			]
		];
		wp_localize_script('woap-builder-script','BUILDObj',$data);
	}

	public function build_details_content_html(){
		#status
		$periority = get_option('woap_build_details_periority');
		$status = get_option('woap_build_details_status');
		$created_at = get_option('woap_build_created_at');
		#show values
		$build_attempt = get_option('woap_build_build_attempt');
		$apk_url = get_option('woap_build_apk_url');
		$created_at_date = $this->service_container->get(BuildLogic::class)->date_localize_format($created_at);
		require_once trailingslashit(WOAB_ADM) . 'build/build-details.php';	
	}	

	public function build_request_content_html(){
		$xml_path = trailingslashit(WOAB_ADM) . 'assets/strings.xml';
		$strings = $this->service_container->get(BuildLogic::class)->parse_xml_dom($xml_path);
		$wp_installation_url = home_url();
		$protocol = parse_url($wp_installation_url,PHP_URL_SCHEME);
		require_once trailingslashit(WOAB_ADM) . 'build/request-form.php';
	}

	public function request_queue_add_resposne(){
		$request_body = $this->request->all();	
		$request_token = $request_body['token'] = $this->service_container->get(BuildLogic::class)->get_request_token();
		update_option('woap_build_request_token',$request_token);
		$wp_installation_url = $request_body['wp_installation_url'] = $request_body['home_url'] = home_url();
		$request_body['response_url'] = trailingslashit(home_url()) . "WOAPApi/Build/updateDetails";
		$request_body['periority'] = 3;
		$request_body['protocol'] = parse_url($wp_installation_url,PHP_URL_SCHEME);
		$http_request = $this->service_container->get(HttpRequest::class);
		$module = 'Queue';
		$action = 'add';
		$request_query = trailingslashit(self::endpoint) . "{$module}/{$action}";
		$request_url = trailingslashit(self::url) . $request_query;
		$response_body = $http_request->set_request_url(trailingslashit($request_url))->post($request_body);
		$response_body_structure = json_decode($response_body,true);
		wp_send_json($response_body_structure);
	}

}
