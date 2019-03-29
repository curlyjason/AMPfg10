<?php
App::uses('EventWatcher', 'Lib');
App::uses('WatchBase', 'Model');

define('QUERY',1);
define('PENDING',2);

/**
 * Discover and manage users who have requested Observer Notification of the user who has just acted
 *
 * Pass in a acting User and a type of Observer Notification and this class will compile a set of
 * EventWatcher nodes. These will all be Users that need to be alerted to the acting User's action
 *
 * @package Notify.Utilities
 * @author dondrake
 */
class EventWatchers {

    public $watchers = array();

    protected $watchPoint;

    /**
     * All the watchers renderd as an array
     *
     * @var array
     */
    public $collection;

    public $appendMode;




    /**
     * Get everyone who observers the provided nodes
     *
     * query, them build all the individual object
     *
     * @param String $type The type of observed object
     * @param array $nodes The observed nodes
     */
    public function __construct() {
        $this->WatchBase = ClassRegistry::init('WatchBase');
    }

    /**
     * Configure all the watchers of watchPoint that also have an observation type in watchTypes
     */
    public function of($watchPoint, $watchTypes){
        $this->appendMode = QUERY;
        $l = $this->WatchBase->watchersOf($watchPoint);
        if (!empty($l)) {
            $this->watchPoint = array(
                'id' => $l['WatchBase']['id'],
                'name' => $l['WatchBase']['username']
            );
            if (!empty($l['Watcher'])) {
                foreach ($l['Watcher'] as $watcher) {
                    $ew = new EventWatcher();
                    $this->append($ew->init($watcher, $watchTypes));
                }
            }
        }
    }

    /**
     * Given a watcher data and type filter, add members to the collection
     *
     * @param array $watchers Watcher data sets
     * @param array $types Types of Observer notifications, filters the watchers
     */
    public function add(Array $watchers, Array $types) {
        $this->appendMode = PENDING;
        if (!empty($watchers)) {
            foreach ($watchers as $watcher) {
                $ew = new EventWatcher();
                $this->append($ew->restore($watcher, $types));
            }
        }
    }

    /**
     * How many watcher objects have been collected?
     *
     * @return int
     */
    public function count() {
        return count($this->watchers);
    }

    /**
     * Turn the collection of data into an array that can be saved and retrieved
     *
     * This allows us to write the data to files for separate processing
     * into many emails without burdening the current user's session with the task
     */
    public function collection() {
        $this->collection = array(
            'WatchPoint' => $this->watchPoint
        );
        foreach($this->watchers as $watcher) {
            $this->collection['Watcher'][] = array(
                'watcher_id' => $watcher->get('watcher_id'),
                'types' => $watcher->get('types')
            );
        }
        return $this->collection;
    }

    /**
     * Get an iterator to get all contacts for all watchers
     *
     * @return \AppendIterator
     */
    public function contactIterator() {
        $it = new AppendIterator();

        foreach ($this->watchers as $watcher) {
            $it->append($watcher->contactIterator());
        }

        return $it;
    }

    /**
     * Add another watcher entry into the collection
     *
     * Can handle watchers coming from 'pending' file reads
     * Or brand new watchers created from queries.
     * There will only be one of each unique watcher.
     * Dups have thier watch points merged into the existing entry
     *
     * @param Available $available
     */
    public function append(EventWatcher $watcher){
        if ($watcher->getWatcher()) {
            // If an entry for this watcher already exists, memorize the type of the new one
            // to use, but we'll accumulate the new watch point to the existing watcher
            if (array_key_exists($watcher->get('watcher_id'), $this->watchers)) {
                $type = $watcher->get('type');
                $properties = array('contact' => FALSE);
                if(!is_null($watcher->get('email'))){
                    $properties['email'] = $watcher->get('email');
                }
                if(!is_null($watcher->get('location'))){
                    $properties['location'] = $watcher->get('location');
                }
                $watcher = $this->watchers[$watcher->get('watcher_id')];
                $watcher->setPropertiesFromArray($properties);
                $watcher->type($type);
            }
            // If this is coming from a query creation (rather than read from a 'pending' file)
            // then there is additional data to gather
            if ($this->appendMode & QUERY) {
                $userData = $this->WatchBase->getUserData($watcher->get('watcher_id'));
                $watcher->addUserData($userData);
                $watcher->addWatchPoint($this->watchPoint, $watcher->type());
            } else {

            }
            // the watcher is properly constructed. Put it in the list
            $this->watchers[$watcher->get('watcher_id')] = $watcher;
        }
    }
}
