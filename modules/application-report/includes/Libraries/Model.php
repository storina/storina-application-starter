<?php 

namespace VOS\Libraries;

abstract class Model {

    public $id;
    protected $data;

    public function __construct(array $data = array()){
        $this->set_data($data);
    }

    public function set_data(array $data){
        foreach($data as $key => $value){
            if(!isset($this->data[$key])){
                continue;
            }
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function get_data($index=null){
        return (isset($index))? $this->data[$index] : $this->data;
    }

    public function save(){
        if(is_numeric($this->id)){
            return static::update($this->id,$this->data);
        }else{
            return static::create($this->data);
        }
    }

    public function remove(){
        static::delete($this->id);
    }

    abstract public static function create($data);
    abstract public static function get($id);
    abstract public static function update($id,$data);
    abstract public static function delete($id);

}
