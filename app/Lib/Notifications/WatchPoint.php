<?php

/*
 * Copyright 2015 Origami Structures
 */

/**
 * Description of WatchPoint
 *
 * @author jasont
 */
class WatchPoint {
	//Properties
	public $identity;
	protected $_id = NULL;
	public $_order_numbers = array();
	protected $_name = NULL;


	//Methods
	public function __construct($id, $name) {
		$this->identity = uniqid();
		$this->_id = $id;
		$this->_name = $name;
	}
	
	/**
	 * Add a single order_number to the _order_numbers array
	 * 
	 * @param type $order_number
	 * @return array the order_numbers list
	 */
	public function add($order_number) {
		if(!isset($this->_order_numbers[$order_number])){
			$this->_order_numbers[$order_number] = $order_number;
		}
		return $this->_order_numbers;
	}
	
	/**
	 * Implementation of the PHP magic getter method
	 * 
	 * @param string $name the name of the requested property
	 * @return mixed (any property)
	 */
	public function __get($name) {
		if(!isset($this->$name)){
			return NULL;
		}
		return $this->$name;
	}
	
	public function getMessage($Messages, $contact) {
		$accumulation = array();
		foreach ($this->_order_numbers as $order_number) {
			$m = $Messages->getMessage($order_number, $contact);
			if ($m !== NULL){
				$accumulation[] = $m;
			}
		}
		return $accumulation;
	}
}
