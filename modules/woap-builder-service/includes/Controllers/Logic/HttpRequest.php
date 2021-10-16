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
		$this->curl_handler = curl_init();
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

	public function set_curl_option($option_key,$option_value){
		curl_setopt($this->curl_handler, $option_key, $option_value);
		return $this;
	}

	public function post($post_fields=[]){
		$this->request_body = array_merge($this->request_body,$post_fields);
		curl_setopt($this->curl_handler, CURLOPT_URL, $this->request_url);
        curl_setopt($this->curl_handler, CURLOPT_POST, true);
        curl_setopt($this->curl_handler, CURLOPT_HTTPHEADER, $this->request_headers);
        curl_setopt($this->curl_handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl_handler, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->curl_handler, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl_handler, CURLOPT_POSTFIELDS, http_build_query($this->request_body));
		$result = curl_exec($this->curl_handler);
		return $result;
	}

	public function __destruct(){
		curl_close($this->curl_handler);
	}

}
