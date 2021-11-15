<?php

namespace WOAP\Controllers\Logic;

use WOAP\Packages\Controller;

class HttpRequest extends Controller{

	public static $response_format;
	private $curl_handler;
	private $request_url; 
	private $request_headers=[];
	private $request_body=[];

	public function __construct($service_container){
		parent::__construct($service_container);
	}	

	public function set_request_url($request_url){
		$this->request_url = $request_url;
		return $this;
	}

	
	public function set_request_headers(Array $request_header){
		$this->request_headers = $request_header;
		return $this;
	}

	public function set_request_body(Array $request_body){
		$this->request_body = $request_body;
		return $this;
	}

	public function add_request_header(Array $request_header){
		$this->request_header[] = array_merge($request_header,$this->request_header);
		return $this;
	}

	public function add_request_body(Array $request_body){
		$this->request_body = array_merge($this->request_body,$request_body);
		return $this;
	}


	public function post($post_fields=[]){
		$this->request_body = array_merge($this->request_body,$post_fields);
		$args = [
			'headers' => $this->request_headers,
			'body' => $this->request_body,
			'sslverify' => false,
		];
		$wp_remote_result = wp_remote_post($this->request_url,$args);
		$respone_body = wp_remote_retrieve_body($wp_remote_result);
		return $respons_body;
	}


}
