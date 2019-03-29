<?php

/**
 * Description of WatchPoint
 *
 * @author jasont
 */
class WatchPoint {
	
	private $id;
	
	private $name;
	
	private $key;
	
	public function __construct($data) {
		$this->id = $data['id'];
		$this->name = $data['name'];
		$this->key = "WP{$this->id}";
	}
	
	public function id() {
		return $this->id;
	}
	
	public function name() {
		return $this->name;
	}
	
	public function slug() {
		return str_replace(' ', '', $this->name());
	}


	public function key() {
		return $this->key;
	}
	
}
