<?php

App::uses('Notify', 'Lib/Notifiers');
App::uses('CakeEmail', 'Network/Email');

/**
 * Establish Email sending setups, based upon Notify abstract class
 * 
 * This is one of the major diferentiation classes for Notification types. 
 * It serves as the base class for all Email Notifications
 * 
 * @package Notification.Notify
 * @author jasont
 */
abstract class NotifyEmail extends Notify{
	
	public $Email;
	
	public function send() {
		$this->Email->config('smtp')
				->template('default', 'default')
				->emailFormat('html')
				->from(array('ampfg@ampprinting.com' => 'AMP FG System Robot'))
				->viewVars(array('content' => $this->message))
				->subject($this->subject);
	}
}
