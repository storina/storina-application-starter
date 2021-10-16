<?php 

namespace Libraries;

abstract class Model {

    public $id;
    protected $data;

    public function __construct(array $data = array()){
        $this->set_data($data);
    }

    public function set_data(array $data){
        foreach($data as $key => $value){
            $this->data[$key] = $value;
        }
        return $this;
    }

    public function get_data($index=null){
        return (isset($index))? $this->data[$index] : $this->data;
    }

    public function save($id=null){
        if(is_numeric($this->id)){
            return static::update($this->data);
        }else{
            return static::create($this->data);
        }
    }

    public function remove(){
        static::delete($this->id);
    }

    abstract public static function create($data=null);
    abstract public static function get($id);
    abstract public static function update($id,$data);
    abstract public static function delete($id);

}