<?php

/*
 * Copyright 2015 Origami Structures
 */

/**
 * Description of Watchers
 *
 * @author jasont
 */
App::uses('Observer', 'Model');
App::uses('Watcher', '/Lib/Notifications');

class Watchers {

	//Properties

	public $identity;

/**
 * The IDs of all accessible users for the current logged in person ???
 *
 * @var array
 */
	protected $_in_list;

/**
 * The Observer Model
 * 
 * Probably could be a simple variable since it's only used in one place
 *
 * @var object Model 
 */
	protected $_Observer;

/**
 * The Observer records for all observers in the logged in person's accessible user list
 *
 * @var array
 */
	protected $_found_observers;

/**
 * The watcher objects indexed by ????
 * 
 * This is read from the cache then has all the found_observers merged in. 
 * On completion of all tasks this property will be sent back to the cache.
 *
 * @var array
 */
	protected $_watchers;

/**
 * Watch value key map
 * 
 * The Observers query makes an array available that gets parsed 
 * into Watcher objects. Watchers provides the extract() method to 
 * get summary information about the set of Watcher object contents.
 * 
 * This map lets extract use more useful names for data points
 *
 * @var array
 */
	protected $map = array(
		'watch_points' => 'user_id',
		'watch_point_names' => 'user_name',
		'types' => 'type',
		'contacts' => 'contact',
		'watcher_names' => 'observer_name',
		'watcher_ids' => 'user_observer_id',
		'locations' => 'location'
	);
	protected $object_map = array(
		'name' => '_name',
		'contact' => '_contact',
		'types' => '_types',
		'watcher_id' => '_watcher_id',
		'watch_points' => '_watch_points'
	);

/**
 * UNUSED ????
 * @var type 
 */
//	protected $_observers_cache;
	//Methods
/**
 * Rehydrate any un-processed watchers and merge in any found (queried) watchers
 * 
 * @param array $in_list Simple array of IDs of all accessible users for the current logged in person
 */
	public function __construct($in_list) {
		$this->identity = uniqid();
		$this->_in_list = $in_list;
		$Observer = ClassRegistry::init('Observer');
		$this->_found_observers = $Observer->findObserversByInList($this->_in_list);
		$this->_watchers = unserialize(Cache::read('observers', 'observers'));
		$this->addObserversToWatchers();
		// this is probably in the wrong place
		Cache::write('observers', serialize($this->_watchers), 'observers');
	}

	public function __toString() {
		$ar = array(
			'Watchers Object',
			'identity' => $this->identity,
			'in_list' => $this->_in_list,
			'found_observers' => $this->_found_observers,
			'Watcher keys' => array_keys($this->_watchers)
		);
		return '<pre>' . var_export($ar, TRUE) . '</pre>';
	}

/**
 * Add all found_observers to the master _watchers list/
 */
	protected function addObserversToWatchers() {
		foreach ($this->_found_observers as $key => $observer) {
			$this->add((object) $observer);
		}
	}

	public function watchers() {
		return $this->_watchers;
	}

/**
 * Property accessor
 * 
 * @param string $name
 * @return mixed The property value or NULL
 */
	public function __get($name) {
		if (!isset($this->$name)) {
			return NULL;
		}
		return $this->$name;
	}

/**
 * Add or merge in this watcher data
 * 
 * @param object $data
 */
	public function add($data) {
		if (!isset($this->_watchers[$data->contact])) {
			$this->_watchers[$data->contact] = new Watcher($data);
		}
		$this->_watchers[$data->contact]->addTypes($data);
	}

/**
 * Get a summary of values present in the set of Watcher objects
 * 
 * Accessible nodes:
 * watch_points, watch_point_names, types, contacts, 
 * watcher_names, watcher_ids, locations
 * 
 * @param string $node 
 */
	public function extract($node) {
		if (array_key_exists($node, $this->map)) {
			$set = Hash::combine($this->_found_observers, "{n}.{$this->map[$node]}", "{n}.{$this->map[$node]}");
			$array_values = array_values($set);
			if (count($set) > 0 && !is_null($array_values[0])) {
				return $set;
			}
		}
		return array();
	}

/**
 * Get a summary of values present in the set of WatchPoint objects
 * 
 * From this source:
 * <pre>
 * 	[protected] _watchers => array(
	  'jason@curlymedia.com' => object(Watcher) {
	  identity => '55ba7665cc6e0'
	  [protected] _watcher_id => '4'
	  [protected] _name => 'Jason Tempestini'
	  [protected] _contact => 'jason@curlymedia.com'
	  [protected] _types => array(
	  'Approval' => array(
	  (int) 46 => '46'
	  ),
	  'Notify' => array(
	  (int) 46 => '46'
	  )
	  )
 * </pre>
 * 
 * Accessible nodes:
 * watch_points, order_numbers)
 * 
 * @param string $node 
 * 
 * @param type $node
 * @return array
 */
	public function extractNew($node) {
		$set = array();
		if (array_key_exists($node, $this->object_map)) {
			$node = $this->object_map[$node];
			foreach ($this->_watchers as $key => $Watcher) {
				switch ($node) {
					case '_types':
						$set = array_merge($set, array_combine(array_keys($Watcher->_types), array_keys($Watcher->_types)));
						break;
					case '_watch_points':
						$set = array_merge($set, Hash::extract($Watcher->_types, '{s}.{n}'));
					default:
						$set[$Watcher->$node] = $Watcher->$node;
						break;
				}
			}
			if($node === '_watch_points'){
				unset($set['']);
				$set = array_combine($set, $set);
			}
			return $set;
		} else {
			return array();
		}
	}

/**
 * Just assuming we'll need to do this (is this a loop?)
 * 
 * Possibly some argument will be needed to target the watcher(contact)
 */
	public function send() {

		// once send is done, unset the member from the collection
		// can a iterator tollerate live deletion? or will we use a different loop?
		// and who does the loop? Why not do it in this collection?
	}

/**
 * Filter the watchers based upon the types provided
 * 
 * If any key in the provided types array matches any key in the
 * Watcher->_type property, return the Watcher object
 * 
 * @param array $type an array of the types we wish Watchers for
 * @return array a keyed array of the Watcher objects, keyed on contact
 */
	public function ofType($type) {
		$result = array();
		foreach ($this->_watchers as $key => $Watcher) {
			$array_intersect_key = array_intersect_key($Watcher->_types, $type);
			if (!empty($array_intersect_key)) {
				$result[$key] = $Watcher;
			}
		}
		return $result;
	}

/**
 * Remove a watcher object from the collection of watchers
 * 
 * @param string $key
 */
	public function unsetWatcher($key) {
		unset($this->_watchers[$key]);
	}

}
