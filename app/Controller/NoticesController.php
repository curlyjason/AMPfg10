<?php
/*
 * Copyright 2015 Origami Structures
 */

App::uses('AppController', 'Controller');
App::uses('EventWatchers', 'Lib');
App::uses('EventWatcher', 'Lib');
App::uses('OrderMessage', 'Lib/Notifiers');
App::uses('Available', 'Lib');
App::uses('Observer', 'Model');
App::uses('NotificationFileHandler', 'Lib/Notifiers');
App::uses('HttpSocket', 'Network/Http');
App::uses('Xml', 'Utility');
App::uses('Hash', 'Utility');
App::uses('Watchers', 'Lib/Notifications');
App::uses('Messages', 'Lib/Notifications');
App::uses('WatchPoints', 'Lib/Notifications');

/**
 * CakePHP NoticesController
 * @author dondrake
 */
class NoticesController extends AppController {
	
	private $useTable = FALSE;
	
	public $helpers = array('Notice');
	
	/**
	 * The file path the current working pending data was read from
	 *
	 * @var string
	 */
	private $path;
	
	/**
	 * The current working pending data
	 *
	 * @var array
	 */
	private $pending;
		
	/**
	 * The timestamp when the current working pending data was originally created
	 * 
	 * This will serve as a comparison datum so if we have overlapping data 
	 * we can figure out which one is the newest version
	 *
	 * @var float
	 */
	private $time;

	private $contacts = array();
	private $Messages = array();
	private $pendingDelete = array();
	private $contact;
	private $messageKeys = array();
	private $emailMessage = array();
	private $Watchers = array();
	
	/**
	 * @var obj NotificationFileHandler
	 */
	private $NotificationFiles;
	private $Observer;
	private $EventWatchers;
	private $WatchPoints;
	private $curl;

	/**
	 *
	 * @var array List of observer types to process in this request 
	 */
	private $types;
	
	/**
	 * Message data that must exist for before a Watcher-contact at an 
	 * observation type to be a required contact
	 *
	 * @var mixed
	 */
	private $messageTypes;
	
	/** ===========================================
	 * These properties must pair with special Observer Model properties
	 ============================================= */
	/**
	 * Indicates what message nodes must exist for a Watcher-contact 
	 * to be collected as a 'required' contact
	 * 
	 * allMessages just returns true, for obvious reasons 
	 *
	 * @var boolean
	 */
	private $allTypes = TRUE;
	
	/**
	 * Indicates what message nodes must exist for a Watcher-contact 
	 * to be collected as a 'required' contact
	 * 
	 * allMessages just returns true, for obvious reasons
	 *
	 * @var boolean
	 */
	private $immediateTypes = array(
		'Approval' => array(
			array ('order.status', 'Submitted')
		),
		'XmlLowInventory' => array(
			array ('LowInventory.', array ()	)
		)
	);
	
	protected $_notification_type = '';
	
	/**
	 * End of special property-pair section
	 * ================================================
	 */

	public function beforeFilter() {
        parent::beforeFilter();
		//establish access patterns
		$this->accessPattern['Manager'] = array ('all');
		$this->accessPattern['Buyer'] = array ('all');
		$this->accessPattern['Guest'] = array ('all');
		
		$this->Observer = ClassRegistry::init('Observer');
		$this->EventWatchers = new EventWatchers();
		$this->NotificationFiles = new NotificationFileHandler();
		$this->processNotificationObjects('read');
    }
	
    public function isAuthorized($user) {
        return $this->authCheck($user, $this->accessPattern);
    }
	
	/**
	 * Read or write objects containing unprocessed Notifications
	 * 
	 * This will be Watchers and Messages. WatchPoints are always 
	 * queried from the db.
	 */
	protected function processNotificationObjects($process){
		//Read Cache Process
		if ($process === 'read') {
			$this->WatchPoints = Cache::read('watch_points', 'notifications');
			if (!$this->WatchPoints) {
				$this->WatchPoints = new WatchPoints();
			}
			
			$this->Watchers = new Watchers($this->WatchPoints->inList());
			
			$this->Messages = Cache::read('messages', 'notifications');
			if (!$this->Messages) {
				$this->Messages = new Messages();
			}
			
		//Write Cache Process	
		} elseif ($process === 'write') {
			$observations = $this->Watchers->extractNew('watch_points');
			$watch_points = $this->WatchPoints->extract('watch_points');
			foreach ($watch_points as $key => $watch_point) {
				if(!in_array($watch_point, $observations)){
					$this->WatchPoints->unsetWatchPoint($watch_point);
				}
			}
			$watched_jobs = $this->WatchPoints->extract('order_numbers');
			$message_jobs = $this->Messages->extract('order_numbers');
			foreach ($message_jobs as $message_order_number) {
				if(!in_array($message_order_number, $watched_jobs)){
					$this->Messages->unsetMessage($message_order_number);
				}
			}
			$extracted_messages = $this->Messages->extract('order_numbers');
			if(empty($extracted_messages)){
				Cache::delete('messages', 'notifications');
				Cache::delete('watch_points', 'notifications');
			} else {
				Cache::write('messages', $this->Messages, 'notifications');
				Cache::write('watch_points', $this->WatchPoints, 'notifications');
			}
		}
	}


	/**
	 * Send any messages that are considered 'Immediate'
	 * 
	 * $types is a string that names an Observer property. This property is 
	 * an array that lists the Observer types that will be processed in this request
	 * 
	 * @param string $types Observer types that will recieve notifications
	 */
	public function send($types){
		$this->_notification_type = $types;
		$this->types = Observer::$$types;
		$this->messageTypes = $this->$types;
		$this->parseAllPending();
		$this->assembleObservers();
		$this->newSendNotifications();
		if($types === 'allTypes'){
			Cache::delete('messages', 'notifications');
			Cache::delete('watch_points', 'notifications');
		} else {
			$this->processNotificationObjects('write');			
		}
	}
		
	/**
	 * Process all the current pending notifications given the current observation 'types' filter
	 */
	private function parseAllPending(){
		
		// read all the files and delete them so no one can overlap our work
		$this->NotificationFiles->readPendingFiles();
		
		// Parse each pending file
		foreach ($this->NotificationFiles->pendingData as $this->path => $this->pending) {
			
			// we'll need to make sure we know which message component is newest if there are dupes
			/// the timestamp is in the file name
			preg_match('/\d+.{1}\d+/', $this->path, $match);
			$this->time = $match[0];
			
			// do the work on the contents of this pending file
			$this->parsePending();
		}
	}
	
	
	private function parsePending() {
		$message = (object) array('time' => $this->time, 'message' => $this->pending['Message']);
		$this->Messages->add($message);
		
		$order_number = FALSE;
		if(isset($this->pending['Message']['LowInventory']) && is_array($this->pending['Message']['LowInventory'])){
			$array_keys = array_keys($this->pending['Message']['LowInventory']);
			$order_number = $array_keys[0];
		} else if(isset($this->pending['Message']['order']) && is_array($this->pending['Message']['order'])) {
			$order_number = $this->pending['Message']['order']['order_number'];
		}
		if($order_number){
			$watch_point = (object) array('watch_point_id' => $this->pending['WatchPoint']['id'], 'order_number' => $order_number);
			$this->WatchPoints->add($watch_point);			
		}
	}
	
	private function assembleObservers() {
		$this->Watchers = new Watchers($this->WatchPoints->inList());
	}
	
	private function newSendNotifications() {
		$this->newConfigureEmail();
		$this->configureCurl();
		
		foreach ($this->Watchers->ofType($this->types) as $key => $Watcher) {
			$message = $Watcher->getMessage($this->Messages, $this->WatchPoints);
            
			if ($message !== FALSE) {
				if (stristr($Watcher->_contact, '@')) {
					$result = $this->sendEmailMessage($Watcher, $message);
				} else {
					$result = $this->sendCurlPost();
				}
				if ($result) {
					CakeLog::write('email', "Successful send ($this->_notification_type) to $Watcher->_contact");
					$this->Watchers->unsetWatcher($key);
				} else {
					$message_text = var_export($message, TRUE);
					CakeLog::write('email', " To: $Watcher->_contact <br/> FAILED TO SEND <br/> Message: <br/> <pre>$message_text</pre>");
				}
			}			
		}
	}
	
	/**
	 * Send the email notification based upon set properties
	 * 
	 * @return boolean
	 */
	private function sendEmailMessage($Watcher, $message) {
		try {
			$this->Email->to($Watcher->_contact)
					->subject('AMP Finished Goods Notifications')
					->viewVars(array(
						'name' => $Watcher->name,
						'messages' => $message
					))
					->send();
		} catch (Exception $exc) {
			CakeLog::write('email', $exc->getMessage());
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * Set the base configurations for email message sending
	 * 
	 */
	private function newConfigureEmail() {
		$this->Email = new CakeEmail();

		$this->Email->config('smtp')
				->template('notification', 'default')
				->emailFormat('html')
				->from(array('ampfg@ampprinting.com' => 'AMP FG System Robot'));
	}
	
	/**
	 * Set the base configuration for Curl posting
	 * 
	 */
	private function configureCurl() {
		$this->curl = curl_init();
		curl_setopt($this->curl,CURLOPT_USERAGENT,'AMP_FG');
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
		// we are doing a POST request
		curl_setopt($this->curl, CURLOPT_POST, 1);
	}
	
	private function sendCurlPost() {
	}
	
	public function sendLogs() {
		$this->Email = new CakeEmail();
		$this->Email->config('smtp')
				->template('default', 'default')
				->emailFormat('html')
				->from(array('ampfg@ampprinting.com' => 'AMP FG System Robot'))
				->subject('AMP FG Logs')
				->to(array('jason@curlymedia.com', 'ddrake@dreamingmind.com'))
				->attachments(array(LOGS . 'error.log', 
					LOGS . 'varlog.log'));
		$this->Email->send();
		
		//setup files for manipulation
		$errorLog = new File(LOGS . 'error.log');
		$varlog = new File(LOGS . 'varlog.log');
		
		//copy logs
		$errorLog->copy(LOGS . 'error_' . time() . '.log');
		$varlog->copy(LOGS . 'varlog_' . time() . '.log');
		
		//empty logs
		$errorLog->write('');
		$varlog->write('');
		$errorLog->close();
		$varlog->close();
	}
	
	public function testMe() {
	}
	
}