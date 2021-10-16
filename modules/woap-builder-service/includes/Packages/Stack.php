<?php

namespace WOAP\Packages;

class Stack {
	
	private $stack;

	public function get($class){
		if(!isset($this->stack[$class])){
			$this->stack[$class] = new $class;
		}
		return $this->stack[$class];
	}

	public function remove($class){
		unset($this->stack[$class]);
	}

	public function set($class,$object){
		$this->stack[$class] = $object;
	}

}

