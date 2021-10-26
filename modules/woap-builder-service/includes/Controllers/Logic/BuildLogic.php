<?php

namespace WOAP\Controllers\Logic;

use DomDocument;

class BuildLogic {

	public function parse_xml_dom($xml_path){
		$xml = new DomDocument();
		$xml->load($xml_path);
		$string_nodes = $xml->getElementsByTagName('string');
		foreach($string_nodes as $string_node){
			$attribute = $string_node->getAttribute("name");
			$strings[$attribute] = $string_node->nodeValue;
		}
		$strings = array_filter($strings, function($value) {
			return !is_null($value) 
				&& $value !== '' 
				&& !ctype_space($value); 
		});
		return $strings;
	}

	public function get_request_token(){
		$home_url = home_url();
		$timestamp = time();
		$current_plugin_version = get_plugin_data(STORINA_FILE)['Version'];
		return md5("{$home_url}-{$timestamp}-{$current_plugin_version}");
	}

	public function date_localize_format($unixtime,$format=null){
		if(empty($unixtime)){
			return;
		}
		$format = $format ?: "Y-m-d H:i:s";
		$string_date = date($format,$unixtimestamp);
		$date = new DateTime($string_date, new DateTimeZone("UTC"));
		$date->setTimezone(new DateTimeZone("Asia/Tehran"));
		$unixtime_with_offset = $date->getTimeStamp() + $date->getOffset();
		return date_i18n($format,$unixtime_with_offset);
	}
}
