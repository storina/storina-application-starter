<?php

namespace WOAP\Packages;

class Request {

    public function input($key,$sanitize_type='string'){
        $value = $_POST[$key] ?? null;
        return (isset($sanitize_type))? $this->sanitize($value,$sanitize_type) : $value;
    }

    public function only($keys,$sanitize_types=[],$only_values){
		foreach($_POST as $key => $value){
			$index = array_search($value , array_values($_POST));
			$sanitize_key = isset($sanitize_type[$index])? $sanitize_type[$index] : 'string'; 
			$results[$key] = $this->input($key,$sanitize_key);
		}	
		return ($only_values)? array_values($results) : $results;
    }

	public function except($keys = null,$sanitize_types=null,$only_values=null){
		$keys = (array) $keys;
		$posted_data = $_POST;
		foreach($posted_data as $key => $value){
			if(isset($posted_data[$key])){
				unset($posted_data[$key]);
			}
		}
		return $this->only(array_keys($posted_data),$sanitize_types,$only_values);
	}

	public function all($sanitize_types=[],$only_values=null){
		$keys = array_keys($_POST);
		return $this->only($keys,$sanitize_types,$only_values);
	}

    protected function sanitize($value,$sanitize_type){
        switch($sanitize_type) {
            case 'string':
                return sanitize_text_field( $value );
            case 'integer':
                return intval($value);
            default:
                return $value;
        }
    }

}
