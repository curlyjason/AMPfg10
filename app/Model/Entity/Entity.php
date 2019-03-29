<?php
/**
 * Description of Entity
 *
 * @author jasont
 */
class Entity {
	
	protected $data = array();
	
	protected $Collection = array();
	
	public function __construct(EntityCollection $collection, $data) {
		$this->Collection = $collection;
		$this->data = $data;
	}
	
	public function __get($key) {
		if(isset($this->data[$key])){
			return $this->data[$key];
		} else {
			return NULL;
		}
	}
	
}
