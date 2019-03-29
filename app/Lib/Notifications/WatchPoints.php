<?php

/*
 * Copyright 2015 Origami Structures
 */

/**
 * The collection class for entities (either companies or people) being observed
 *
 * @author jasont
 */
App::uses('WatchPoint', '/Lib/Notifications');

class WatchPoints {

	//Properties
	public $identity;
	protected $_watch_points = array();
	protected $_key = NULL;
	protected $_index = 0;

	//Methods

	public function __construct() {
		$this->identity = uniqid();
		$this->setupWatchPoints();
	}

	public function __wakeup() {
		$this->setupWatchPoints();
	}

/**
 * Setup stub watch points with names of watched entities
 * 
 * This will run both on construction and on wake up
 */
	private function setupWatchPoints() {
		$WP = ClassRegistry::init('Observers');
		$all_watch_points = $WP->find('list', array('fields' => array('user_id', 'user_name')));
		foreach ($all_watch_points as $wp_id => $wp_name) {
			if (!isset($this->_watch_points[$wp_id])) {
				$this->_watch_points[$wp_id] = new WatchPoint($wp_id, $wp_name);
			}
		}
	}

	public function __toString() {
		$ar = array(
			'WatchPoints Object',
			'identity' => $this->identity,
			'key' => $this->_key,
			'index' => $this->_index,
//			'Messages' => $this->Messages->identity,
			'WatchPoint keys' => array_keys($this->_watch_points)
		);
		return '<pre>' . var_export($ar, TRUE) . '</pre>';
	}

/**
 * Add individual watch points to the watch_points collection
 * 
 * The watch_points collection contains single objects for each watch point, indexed
 * by their ID. The function insures there is a watch point object, then calls the
 * watch point's add method to integrate the provided order number properly.
 * 
 * @param object $data the single watch point with ID and order number
 * @return object the _watch_points collection object
 */
	public function add($data) {

		if (!isset($this->_watch_points[$data->watch_point_id])) {
			$this->_watch_points[$data->watch_point_id] = new WatchPoint($data->watch_point_id, $this->watcherName($data->watch_point_id));
		}
		$this->_watch_points[$data->watch_point_id]->add($data->order_number);
		return $this->_watch_points[$data->watch_point_id];
	}

	private function watcherName($watch_point) {
		$User = ClassRegistry::init('User');
		return $User->discoverName($watch_point);
	}

/**
 * Get the protected _watch_points collection object
 * 
 * @return array the array of all watch_point objects
 */
	public function watch_points() {
		return $this->_watch_points;
	}

/**
 * Get a single watch_point object
 * 
 * Retreive the single watch_point object indexed by its ID from the protected
 * _watch_points collection array
 * 
 * @param  $watch_point_id
 * @return object single watch_point object
 */
	public function get($watch_point_id) {
		if (!isset($this->_watch_points[$watch_point_id])) {
			$this->_watch_points[$watch_point_id] = new WatchPoint($watch_point_id, $this->watcherName($watch_point_id));
		}
		return $this->_watch_points[$watch_point_id];
	}

/**
 * Get the list of watch_point IDs
 * 
 * @return array inList of all watch_point IDs
 */
	public function inList() {
		return array_keys($this->_watch_points);
	}

/**
 * Get a summary of values present in the set of WatchPoint objects
 * 
 * Accessible nodes:
 * watch_points, order_numbers)
 * 
 * @param string $node 
 * 
 * @param type $node
 * @return array
 */
	public function extract($node) {
		if ($node === 'watch_points') {
			return $this->inList();
		} elseif ($node === 'order_numbers') {
			$accum = array();
			foreach ($this->inList() as $watchPoint) {
				if (isset($this->_watch_points[$watchPoint]) && isset($this->_watch_points[$watchPoint]->_order_numbers) && is_array($this->_watch_points[$watchPoint]->_order_numbers)) {
					$accum = array_merge($accum, $this->_watch_points[$watchPoint]->_order_numbers);
				}				
			}
			return $accum;
		} else {
			return array();
		}
	}

	public function getMessage($watch_point, $Messages, $contact) {
		if (!isset($this->_watch_points[$watch_point])) {
			return '';
		}
		return $this->_watch_points[$watch_point]->getMessage($Messages, $contact);
	}

	public function unsetWatchPoint($key) {
		unset($this->_watch_points[$key]);
	}

	public function __get($name) {
		if (isset($this->$name)) {
			return $this->$name;
		} else {
			return NULL;
		}
	}

}
