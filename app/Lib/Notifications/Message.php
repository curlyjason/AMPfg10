<?php

/*
 * Copyright 2015 Origami Structures
 */

/**
 * Message class
 * 
 * The Message class is a Heap of all notifications in this object,
 * with the most recent (based upon provided timestamp) notification on the top
 * of the heap.
 *
 * @author jasont
 */
class Message {
	//Properties
	public $identity;
	protected $_message;
	public $count;
	public $most_recent;
	public $sent_to = array();
	
	//Methods
	public function __construct() {
		$this->identity = uniqid();
		$this->_message = new MessageHeap();
	}
	
	/**
	 * When returning from cache read, insert the saved $most_recent message back
	 * into the heap to prepare for the addition of more messages
	 * 
	 */
	public function __wakeup() {
		if (isset($this->most_recent)) {
			$this->rehydrate($this->most_recent);
		}		
	}
	
	/**
	 * Add the provided notification to this order's message object
	 * 
	 * The add() function calls the object insert() method, which places this
	 * notification in the proper order in the heap.
	 * 
	 * @param object $data the notification to be added
	 */
	public function add($data) {
		$this->rehydrate($data);
		$this->sent_to = array();
	}
	
	public function rehydrate($data) {
		$this->_message->insert($data);
		$this->count = $this->count();
		$this->most_recent = $this->mostRecent();
	}

		public function getMessage($contact) {
		if (!in_array($contact, $this->sent_to)) {
			array_push($this->sent_to, $contact);
			if (isset($this->most_recent)) {
				return $this->most_recent;
			}
		}		
//		return (object) array();
		return NULL;
	}

	/**
	 * Return the most recent notification
	 * 
	 * Using the top() function allows us to return the most recent notification
	 * without removing it from the heap.
	 * 
	 * @return object the most recent notification from the message object
	 */
	public function mostRecent() {
		return $this->_message->top();
	}
	
	/**
	 * Return the most recent message (as sorted in the heap)
	 * 
	 * Uses the 'top' function to non-destructively return the top object from the
	 * heap and leave the entire heap intact.
	 * 
	 * @param string $node currently 'top' to pull the most recent
	 * @return object the Message object
	 */
//	public function getMessage($node = 'top') {
//		if($node == 'top'){
//			$message = $this->mostRecent();
//		} else {
//			//We need to include some method here to return more than the most recent message
//			$message = [];
//		}
//		return $message;
//	}
	
	/**
	 * All methods below are instantiations of standard heap methods, called from
	 * the context of this class.
	 * 
	 * @return varies
	 */
	public function count() {
		return $this->_message->count();
	}
	
	public function valid() {
		return $this->_message->valid();
	}
	
	public function next() {
		return $this->_message->next();
	}
	
	public function current() {
		return $this->_message->current();
	}
	
	public function rewind() {
		return $this->_message->rewind();
	}
	
}

class MessageHeap extends SplHeap {

	/**
	 * An override of the compare function in the SplHeap base class.
	 * 
	 * This method employs the time property of the message object to determine where
	 * in the time-sorted heap of messages the newly added message should go.
	 * 
	 * @param object $value1 the element being added
	 * @param object $value2 the element in the heap being compared to
	 * @return int
	 */
	protected function compare($value1, $value2) {
		if ($value1->time === $value2->time){
            return 0;
        }
		return $value1->time > $value2->time ? 1 : -1;
	}

}
