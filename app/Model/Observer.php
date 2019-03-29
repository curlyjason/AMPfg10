<?php

App::uses('AppModel', 'Model');

/**
 * Observer Model
 *
 * @property User $User
 * @property UserObserver $UserObserver
 */
class Observer extends AppModel {

// <editor-fold defaultstate="collapsed" desc="Associations">
/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'UserObserver' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'ObservingUser' => array(
			'className' => 'User',
			'foreignKey' => 'user_observer_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);


// </editor-fold>
// <editor-fold defaultstate="collapsed" desc="Properties">
/** ===========================================
 * These properties must pair with special NoticesController properties
	  ============================================= */

/**
 * An array of potential observer types
 * 
 * @var array 
 */
	public static $allTypes = array(
		'Approval' => 'Approval',
		'Notify' => 'Notify',
		'XmlLowInventory' => 'XmlLowInventory'
	);
	public static $immediateTypes = array(
		'Approval' => 'Approval',
		'XmlLowInventory' => 'XmlLowInventory'
	);
/**
 * End of special property-pair section
 * ================================================
 */

/**
 * Observer types that receive email notification in output priority order
 *
 * @var array
 */
	protected static $emailTypes = array('Approval', 'Notify');

/**
 * Observer types that receive notification sent to a URL
 *
 * @var array
 */
	protected static $urlTypes = array('XmlLowInventory');

/**
 * The Observation types that get notified during Status changes
 * 
 * When status changes occurs, only observers requesting these types 
 * will cause a Pending file to be written (for later notification)
 * 
 * Other typesFor[foo]Pending are used for other events
 *
 * @var array
 */
	public $typesForStatusPending = array(
		'Notify',
		'Approval'
	);

/**
 * The Observation types that get notified during low inventory detection
 * 
 * When ordered quantities for products change, the underlying item may go 
 * below the set reorder level. The observers requesting these types 
 * will cause a Pending file to be written (for later notification)
 * 
 * Other typesFor[foo]Pending are used for other events
 *
 * @var array
 */
	public $typesForLowInvPending = array(
		'Notify',
		'XmlLowInventory',
		'Csv',
		'Json'
	);

/**
 * The Observation types and their triggers
 * 
 * Currently called in AppController 1372 in the old email notification process. 
 * That processes is slated for re-write/re-distribution into the Event system
 *
 * @var array
 */
	public $observationTriggers = array(
		'Approval' => array(
			'Submitted'
		),
		'Notify' => array(
			'Submit',
			'Approve'
		),
		'XML' => array(
			'Complete'
		// low inventory also
		)
	);

// </editor-fold>

	public function beforeSave($options = array()) {
		parent::beforeSave($options);

		// Always set names to the best name available in the User table
		$this->data['Observer']['observer_name'] = $this->User->discoverName($this->data['Observer']['user_observer_id']);
		$this->data['Observer']['user_name'] = $this->User->discoverName($this->data['Observer']['user_id']);
	}

/**
 * Get user leaves that have email in username as a list
 * 
 * Only usernames with emails will work as observers
 * since notifications are always emailed
 * 
 * @param type $rootNodes
 * @return type
 */
	public function getAccessibleObservers($rootNodes) {

		// returns flat list of accessible users
		$flatNodes = $this->User->getAccessibleUserNodes($rootNodes, array('User.active' => 1));

		// group list by parent
		return $this->User->nodeGroups($flatNodes);
	}

	public static function emailTypes() {
		return self::$emailTypes;
	}

	public static function urlTypes() {
		return self::$urlTypes;
	}

	public function findObserversByInList($in_list) {
		$output = array();
		$observers = $this->find('all', array(
			'conditions' => array('user_id' => $in_list),
			'contain' => array('ObservingUser')));
		foreach ($observers as $key => $observer) {
			$output[$key] = $observer['Observer'];
			$output[$key]['contact'] = (empty($observer['Observer']['location'])) ? $observer['ObservingUser']['username'] : $observer['Observer']['location'];
		}
		return $output;
	}

}
