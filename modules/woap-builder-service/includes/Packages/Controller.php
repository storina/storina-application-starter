<?php

namespace WOAP\Packages;

class Controller {

	public $service_container;
	public $request;
	public $stack;

	public function __construct($service_container){
		$this->service_container = $service_container;		
		$this->request = new Request;
		$this->stack = new Stack;
	}

}
