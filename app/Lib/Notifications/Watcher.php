<?php

/*
 * Copyright 2015 Origami Structures
 */

/**
 * Watcher represents a single address that must receive notification
 * 
 * This is a single contact for a single watcher, and all that watcher's 
 * WatchPoints indexed by the typ of observation. It is one place 
 * we will send notifications and information about all the 
 * notifications that will be sent.
 * 
 * It's important to remember that a watcher (a User record) may require notification 
 * at different addresses. They may want to get messages about different company 
 * activity sent to different email addresses. Or they may want notifications sent 
 * to automated systems that can be found at URLs.
 * 
 * <pre>
 * Watcher (object)
 *		_watcher_id	=	4
 *		_name		=	Jason Tempestini
 *		_contact	=	jason@curlymedia.com //could be a url for machine->machine notifications
 *		_types		=	[
 *						 'Approval' => [ '13' => '13', '46' => '46'],
 *						 'Notice' => [ '6' => '6' ]
 *						]
 * </pre>
 *
 * @author jasont
 */

App::uses('Messages', 'Lib/Notifications');

class Watcher {
	//Properties
	public $identity;
	protected $_watcher_id;
	protected $_name;
	protected $_contact;
	protected $_types;
	
	//Methods
	public function __construct($data) {
		$this->identity = uniqid();
		$this->_watcher_id = $data->user_observer_id;
		$this->_name = $data->observer_name;
		$this->_contact = $data->contact;
	}
	
	public function __toString() {
		$ar = array(
			'Watcher Object',
			'identity' => $this->identity,
			'watcher_id' => $this->_watcher_id,
			'name' => $this->_name,
			'contact' => $this->_contact,
		);
		return '<pre>'.var_export($ar, TRUE).'</pre>';
	}

		public function addTypes($data) {
		if(!isset($this->_types[$data->type])){
			$this->_types[$data->type] = array();
		}
		$this->_types[$data->type][$data->user_id] = $data->user_id;
	}
	
	/**
	 * Return the watchPoints based upon the desired message type
	 * 
	 * @param string $type the desired type
	 * @return array the associative array of watch points based upon the type
	 */
	public function getWatchPoints($type) {
		if (isset($this->_types[$type])) {
			return $this->_types[$type];
		} else {
			return FALSE;
		}
	}
	
	public function getMessage($Messages, $WatchPoints) {
		$accum = array();
		foreach ($this->_types as $type => $watch_points) {
			foreach ($watch_points as $watch_point) {
				$test = array();
				$test = isset($WatchPoints->get($watch_point)->_order_numbers) ? $WatchPoints->get($watch_point)->_order_numbers : array();
				if (!empty($test)) {
					$m = $WatchPoints->getMessage($watch_point, $Messages, $this->_contact);
					$name = $WatchPoints->get($watch_point)->_name;
					if (!empty($m)) {
						$accum[$type][$name] = $m;
					}
				}				
			}
		}
		if($accum === array()){
			return FALSE;
		}
		return $accum;
	}
	
	/**
	 * Implementation of the magic get method
	 * 
	 * @param string $name of the property to get
	 * @return mixed
	 */
	public function __get($name) {
		if(isset($this->$name)){
			return $this->$name;
		}else{
			return NULL;
		}
	}
}
