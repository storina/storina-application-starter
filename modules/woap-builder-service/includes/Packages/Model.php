<?php

namespace WOAP\Packages;

class Model {

	public $id;
	protected $data;

	protected $have_map;
	protected $have_stack;
	protected $have_key;

    public function __construct(array $data = array()){
        $this->set_data($data);
    }

    public function get_id(){
        return $this->id;
    }

    public function set_data(array $data){
        foreach($this->data as $key => $value){
            $this->data[$key] = $data[$key] ?? $value;
        }
        return $this;
    }

    public function get_data($index=null){
        return (isset($index))? $this->data[$index] : $this->data;
    }

    public function save($id=null){
        if(is_numeric($this->id)){
            $result = $this->update($this->id,$this->data);
			return ($result)? $this : false;
        }else{
            $this->id = $this->create($this->data);
			return $this;
        }
    }

    public function remove(){
        $this->delete($this->id);
    }

	public function __call($resource,$args){
		if($this->have_stack[$resource]){
			return $this->have_stack[$resource];
		}	
		$class = $this->have_map[$resource];
		if(!isset($class)){
			return;
		}
		$object = new $class;
		$foreign_key = $this->have_key[$resource];
		$object->set_data([$foreign_key => $this->id]);
		$this->have_stack[$resource] = $object;
		return $object;
	}

    public function create($data){}
    public function update($id,$data){}
    public function delete($id){}

}
