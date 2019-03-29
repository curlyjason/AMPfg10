<?php

/**
 * LowInventoryEmail
 * 
 * The concrete class to handle email notifications of low inventory states
 *
 * @todo Does this need to become a Controller or get instantiated by a controller to use the render engine? Or possibly it's not an event?
 * @package Notification.Notify
 * @author jasont
 */
App::uses('NotifyEmail', 'Lib/Notifiers');

class NotifyEmailLowInventory extends NotifyEmail{
	
	public function send() {
		debug($this->watchPoint);
		
		$this->Email = new CakeEmail();
		
		$this->subject = "Low Inventory Alert: {$this->watchPoint['name']}";
		parent::send();
		
		foreach ($this->watchers as $watcher) {
			$m = microtime(TRUE);
			$this->Email->to($watcher->get('email'))
				->send($this->message);	
			debug(microtime(TRUE) - $m . ' Time to send email, microseconds');
		}
	}

	public function implementedEvents() {
		
	}

}

