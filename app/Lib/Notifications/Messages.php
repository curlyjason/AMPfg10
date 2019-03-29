<?php
/*
 * Copyright 2015 Origami Structures
 */

/**
 * Messages class
 * 
 * The Messages class is a collection class containing all of the message objects indexed
 * by order number
 * 
 * <pre>
 * (object) array(
 *	1502-AAFW => (object) array(
 *		time => timestamp of message,
 *		message => array(
 *			array of message data
 *			)
 *		),
 *	1502-AAFZ => (object)....
 * </pre>
 * 
 * This class manages the addition and retreival of all message objects. 
 *
 * @author jasont
 */
App::uses('Message', 'Lib/Notifications');


class Messages {
	
	//Properties
	public $identity;
	protected $_messages = NULL;
	protected $_message = NULL;
	protected $_order_number = NULL;
	
	//Methods
	
	public function __construct() {
		$this->identity = uniqid();
	}
	
	public function __toString(){
		$ar = array(
			'Messages Object',
			'identity' => $this->identity,
			'order_number' => $this->_order_number,
			'Message keys' => array_keys($this->_messages)
		);
		return '<pre>'.var_export($ar, TRUE).'</pre>';
	}
	/**
	 * Add a single messsage to the order number indexed message object in the messages property
	 * 
	 * In order to combine all order messages by order number, this method uses the 
	 * has() method to check for the existance it's order number in the messages property, 
	 * then adds the provided message to that collection.
	 * 
	 * @param object $data the objectified message with message array and time
	 * @return object the _message object with the provided message added
	 */
	public function add($data) {
		$array_keys = array_keys($data->message);
		$index = $array_keys[0];
		switch ($index) {
			case 'order':
				return $this->addForOrder($data, $index);
				break;
			case 'LowInventory':
				return $this->addForLowInventory($data, $index);
                break;
			default:
				break;
		}
	}
	
	/**
	 * Add a single messsage to the order number indexed message object in the messages property
	 * 
	 * In order to combine all order messages by order number, this method uses the 
	 * has() method to check for the existance it's order number in the messages property, 
	 * then adds the provided message to that collection.
	 * 
	 * @param object $data the objectified message with message array and time
	 * @param string $index the type of message provided
	 * @return object the _message object with the provided message added
	 */
	private function addForOrder($data, $index) {
		$this->_message = $this->has($data->message[$index]['order_number']);
		$this->_order_number = $data->message[$index]['order_number'];
		$this->_message->add($data);
		return $this->_message;
	}
	
	/**
	 * Add a single messsage to the item number indexed message object in the messages property
	 * 
	 * In order to combine all item messages by item number, this method uses the 
	 * has() method to check for the existance it's item number in the messages property, 
	 * then adds the provided message to that collection.
	 * 
	 * @param object $data the objectified message with message array and time
	 * @param string $index the type of message provided
	 * @return object the _message object with the provided message added
	 */
	private function addForLowInventory($data, $index) {
		if (empty($data->message[$index])) {
			return;
		}
		foreach (array_keys($data->message[$index]) as $index => $inventory_key) {
			$this->_message = $this->has($inventory_key);
			$this->_order_number = $inventory_key;
			$this->_message->add($data);
		}
		return $this->_message;
	}
	
	/**
	 * Return a message object based upon a provided order number
	 * 
	 * Return the existing message object, or, if one does not exist, create a new
	 * message object and return that.
	 * 
	 * @param string $order_number the order number to search for
	 * @return object the order number specified message object
	 */
	public function has($order_number) {
		if(!isset($this->_messages[$order_number])){
			$this->_messages[$order_number] = new Message($this);
		}
		$this->_message = $this->_messages[$order_number];
		$this->_order_number = $order_number;
		return $this->_messages[$order_number];
	}
	
	/**
	 * Return the most recently chosen message object
	 * 
	 * @return object the most recently addressed message object
	 */
	public function last() {
		return $this->_message;
	}
	
	public function extract($node = 'order_numbers') {
		if ($node === 'order_numbers') {
            if(!is_null($this->_messages)){
                return array_keys($this->_messages);
            }
		}
		return array();
	}

	/**
	 * Return the order number of the most recently addressed message object
	 * 
	 * @return string the order number
	 */
	public function lastOrderNumber() {
		return $this->_order_number;
	}
	
	/**
	 * Retreive the condensed message from the specified Message object
	 * 
	 * This calls the getMessage method from the Message object, which returns only
	 * the latest message for a specific order number or item number.
	 * 
	 * @param string $order_number the requested order number or item number
	 * @return object the selected Message object
	 */
	public function getMessage($order_number, $contact) {
		if (!isset($this->_messages[$order_number])) {
			return NULL;
		}
		return $this->_messages[$order_number]->getMessage($contact);
	}
	
	public function unsetMessage($key) {
		unset($this->_messages[$key]);
	}
}
