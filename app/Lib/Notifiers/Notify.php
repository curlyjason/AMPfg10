<?php

App::uses('NotificationFileHander', 'Lib/Notifiers');
App::uses('AppController', 'Controller');
App::uses('Budget', 'Model');
App::uses('User', 'Model');
App::uses('Order', 'Model');

/**
 * Abstract class for all notification events
 * 
 * These are the Events that will actually communicate back to the client. 
 * The goal is to have this process run outside of normal site activity to prevent 
 * site users from experienceing processing delays associated with email transactions 
 * or connection delays that may arrise from posting data to client servers.
 * 
 * Additionally, context data that was saved when the original 'notification' event was 
 * dispatched is, in some cases a bit sketchy. Additional queries will need to be performed 
 * to fill out the data for construction of the final messages. This task was also deferred.
 * 
 * @package Notification.Notify
 * @author jasont
 */
abstract class Notify extends AppController implements CakeEventListener{
	
	protected $watchPoint;
	
	protected $watchers;
	
	protected $message;
	
	/**
	 * EXPECTED: Read all Pending and parse into Processed
	 * EXPECTED: The Event Handler will have fired up the desired concrete sub class
	 */
	public function __construct() {
//		$this->watchPoint = $watchPoint;
//		$this->watchers = $watchers;
//		$this->message = $message;
	}
	
	/**
	 * Will this be our single public method? or will there only be a constructor?
	 */
	abstract function send();
	
}
