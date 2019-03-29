<?php
/**
 * 'Node' class for EventWatchers
 * 
 * Encapsulate a single 'Watcher. A watcher is a user that has 
 * requested any of the forms of Observer Notification. 
 * This Object is constructed and contained by EventWatchers.
 *
 * @package Notification.Utilities
 * @author dondrake
 */
class EventWatcher {
	
// <editor-fold defaultstate="collapsed" desc="Properties">


	/**
	 * Indicates whether the object was populated with data or not
	 *
	 * @var boolean TRUE = good to go, FALSE = didn't survive 'type' filtering
	 */
	protected $watcher;


	/**
	 * Is this used?
	 *
	 * @var type 
	 */
	protected $observered = array(); // observered point and their types


	/**
	 *
	 * @var string User.id of this watcher
	 */
	protected $watcher_id;


	/**
	 * The working watch point
	 * 
	 * For multi step processes, we want to know what watch point data we are 
	 * working with, given that each wathcer eventually can have many. 
	 * $wp['id'=>xx, 'name'=>company_name]
	 *
	 * @var string
	 */
	protected $watchPoint;


	/**
	 * watch point keys for each watch type
	 * 
	 * array (
	 *  'Notify' => array (
	 * 		wp_id => company_name,
	 * 		wp_id => company_name
	 *  ),
	 * 	'Approve' => array( ...)
	 * );
	 * 
	 * @var array
	 */
	protected $watchPoints;

	/**
	 *
	 * @var string Watcher name
	 */
	protected $name;


	/**
	 * The working observation type
	 * 
	 * For multi step processes, we want to know what type we are considering, 
	 * given that each wathcer can watch at many observation types 
	 *
	 * @var string
	 */
	protected $type;


	/**
	 * Oversvation types, thier contact address and the watch points for the each type
	 * 
	 * $this->type = [
	 * 	Notify => [
	 * 		contact => me@here.org
	 * 		watchpoints => [ xx, yy, zz]]
	 * 	Approval => [
	 * 		contact , watchpoints ]
	 *  
	 * @var array Lookup for contacts and watchpoints for different ovservation types
	 */
	protected $types;

		/**
	 * URL notification point for this observer
	 *
	 * @var string
	 */
	protected $location;


	/**
	 * Email notification point for this observer
	 *
	 * @var string
	 */
	protected $email;


	/**
	 * The proper notification point for this wathcer given their observation type
	 *
	 * @var string
	 */
	protected $contact = FALSE;
	
	protected $watchTypes = array();

	// </editor-fold>
	public function __construct() {
	}
	
	/**
	 * Given data an a watchType filter set up a new EventWatcher (does not automatically add watch points)
	 * 
	 * data expected to have: watcher_id, name, type, location 
	 * email is stored in a separate table and is set later
	 * 
	 * @param array $watcher Data provided by WatchBase Model
	 * @param array $watchTypes Filter array to limit who is considered a watcher in this context
	 */
	public function init($watcher, $watchTypes) {
		$this->watchTypes = $watchTypes;
		// only set up if the new record passes the filter
		if (in_array($watcher['type'], $watchTypes)) {
			$this->watcher = TRUE;
			$this->setPropertiesFromArray($watcher);
					
		// if it can't pass the filter, let the caller no the object was not created
		} else {
			$this->watcher = FALSE;
		}
		return $this;
	}


    /**
     * @param $watcher
     * @param $watchTypes
     * @return $this|bool
     * @todo add cake logging
     */
	public function restore($watcher, $watchTypes) {
		// ERROR ARRAY KEYS ARG 1 NOT ARRAY, STRING GIVEN (Placed a guard to prevent this) **
		if (is_string($watcher['types'])) {
		    //add cake logging
			return $this->watcher = FALSE;
		}
		$keys = array_keys($watcher['types']);
		// ERROR ARG 1 NOT ARRAY, STRING GIVEN (Placed a guard to prevent this) **
		if (is_string($keys)) {
		    //add cake logging
			return $this->watcher = FALSE;
		}
		$int = array_intersect($keys, $watchTypes);
		if(empty($int)){
			$this->watcher = FALSE;
		} else {
		$this->setPropertiesFromArray($watcher);
		$this->watcher = TRUE;
		}
		return $this;
	}

		/**
	 * Add properties based on data in an array
	 * 
	 * @param array $array 
	 */
	public function setPropertiesFromArray($array) {
		foreach ($array as $key => $value) {
			if (property_exists($this, $key)) {
				$this->{$key} = $value;
			}
		}
	}
	
	/**
	 * Setup all points or add a point to this watcher object
	 * 
	 * This is the public call point to set a watch point entry. 
	 * It handles 3 scenarios so it can do the job now and in the future. 
	 * If no args are provided, a query will set up all watch points for this watcher (NOT IMPLEMENTED). 
	 * Passing a watch point ( $wp['id'=>xx, 'name'=>company_name] ) will add to the watch-point list
	 * In the second case, pass an observation type or let the current type property point the way
	 * 
	 * @param array $watchPoint
	 * @param string $type
	 */
	public function addWatchPoint($watchPoint = NULL, $type = NULL) {
		// request for complete config of all points
		if (is_null($watchPoint)) {
			// query and set up all point. NOT IMPLEMENTED
			return FALSE;
		// request to add to a specific type's point-list
		} else if (!is_null($type)) {
			$this->type = $type;
		}
		
		// fall through for above, or fully specified request
		$this->watchPoint = $watchPoint;
		$this->addWP();
	}
	
	/**
	 * Internal procedure to set a watch point entry using controlled properies
	 */
	private function addWP() {
		// if this is a new observation type, set it up
		if (!array_key_exists($this->type, $this->watchTypes)) {
			$this->types[$this->type]['watchpoint'] = array("WP{$this->watchPoint['id']}" => $this->watchPoint['name']);
			$this->types[$this->type]['contact'] = $this->_contact();
			
		// if it's an existing type, setup or overwrite the watchpoint
		} else {
			$this->types[$this->type]['watchpoint']["WP{$this->watchPoint['id']}"] = $this->watchPoint['name'];
			$this->types[$this->type]['contact'] = $this->_contact();
		}
	}
	

	/**
	 * Is this used?
	 * 
	 * @return type
	 */
	public function getObserved(){
		return $this->observed;
	}
	
	/**
	 * Discover if the object was configured or failed the filter
	 * 
	 * @return boolean
	 */
	public function getWatcher(){
		return $this->watcher;
	}
	
	/**
	 * Get the proper contact point (email or url) for this wathcer using observation $type
	 * 
	 * This data is also stored in the $types array and it is probably better to 
	 * get it out of there.
	 * 
	 * @param string $type The observation type that we need the contact for
	 * @return string|boolean The contact appropriate to this observation type or false
	 */
	public function contact($type) {
		if (!is_string($type)) {
			return FALSE;
		}
		
		// if none is set or we were never able to set a valid one return null
		if (!isset($this->types[$type]['contact']) || is_null($this->types[$type]['contact'])) {
			CakeLog::write(LOG_ALERT, "No 'contact' was available for $this->name using the '$type' observation type.");
			return NULL;
		}
		
		// return the contact
		return $this->types[$type]['contact'];
	}
	
	/**
	 * Get an iterartor for the contacts stored in this Watcher
	 * 
	 * @return \ContactIterator
	 */
	public function contactIterator() {
		return new ContactIterator($this->types, 'contact');
	}

	/**
	 * Internal process to discover contact for a new observation type
	 * 
	 * @return string|boolean The contact appropriate to this observation type or false
	 */
	public function _contact() {
		
		if (!$this->contact) {
			if (in_array($this->type, Observer::emailTypes())) {
				$this->contact = $this->email;
			} elseif (in_array($this->type, Observer::urlTypes())) {
				$this->contact = $this->location;
			} else {
				CakeLog::write(LOG_ALERT, "$this->name engages in $this->type Observervation, but they do not have the proper email or location set to send these notifications.");
				$this->contact = NULL;
			}
		}
		return $this->contact;
	}
	
	/**
	 * Return any property
	 * 
	 * @param string $property
	 * @return string|null
	 */
	public function get($property) {
		if (property_exists($this, $property)) {
			return $this->{$property};
		} else {
			return NULL;
		}
	}
	
	/**
	 * Add more properties data to the object from the User record
	 * 
	 * @param array $userData
	 */
	public function addUserData(Array $userData) {
		if (isset($userData['WatchBase']['username'])) {
			$this->email = $userData['WatchBase']['username'];
		} else {
			$this->email = '';
		}
		
	}

	/**
	 * Is this used?
	 * 
	 * @param type $id
	 * @return type
	 */
	public function isObservering($id) {
		return in_array($id, $this->observered);
	}
	
	/**
	 * Determine if this watcher is an observer of $type for any of $watchPoints
	 * 
	 * @param string $type the observation type
	 * @param array $watchPoints the array of user ids that might be observed
	 * @return boolean
	 */
	public function observationCheck($type, Array $watchPoints) {
		if(!array_key_exists($type, $this->types)){
			return FALSE;
		}
		foreach ($watchPoints as $watchPoint) {
			if (array_key_exists('WP' . $watchPoint, $this->types[$type]['watchpoint'])) {
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * Set or Get the value of the type property
	 * 
	 * @param string $value
	 * @return string the value of the $type property
	 */
	public function type($value = NULL) {
		if($value !== NULL){
			$this->type = $value;
		}
		return $this->type;
	}
}

/**
 * Get the contact for each Notification type
 * 
 * The contact value is a second level index. 
 * The key returned will be the first level index (notification type) 
 * and the value will be the contact for that type
 */
class ContactIterator implements Iterator {
	public $data;
	public $position;
	public $keys;
	public $target;
	public $count;

	public function __construct($array, $target){
		$this->data = $array;
		$this->keys = array_keys($array);
		$this->count = count($this->keys);
		$this->position = 0;
		$this->target = $target;
	}

	public function rewind() {
		$this->position = 0;
	}

	public function key() {
		return $this->keys[$this->position];
	}

	public function next(){
		++$this->position;
	}

	public function current() {
		return $this->data[$this->keys[$this->position]][$this->target];
	}

	public function valid() {
		return ($this->position < $this->count);
	}
}
