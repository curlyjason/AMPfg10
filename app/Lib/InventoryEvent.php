<?php
App::uses('CakeEventListener', 'Event');
App::uses('EventWatchers', 'Lib');
App::uses('NotificationFileHandler', 'Lib/Notifiers');

/**
 * Do record keeping when Item quantities change or when other site activities will effect inventory
 * 
 * When any customers order stock, we maintain a datum on the Item to indicate the obligation (available_qty) 
 * and call for information to be gathered and stored for DOM update process, Observer Notification (and more later?)
 *
 * @package Inventory.Utilities
 * @author dondrake
 */
class InventoryEvent implements CakeEventListener {
	
	protected $messages = array();
	

	public function implementedEvents() {
		return array(
			'Item.Availability' => 'lowQtyCheck');
	}
	
	public function lowQtyCheck($event) {
		$this->Observer = ClassRegistry::init('Observer');
		$this->AvailableEntries = $event->subject;

		// It makes a difference who might call this
		// If Order submission and individual item qty changes are the only triggers
		// then visits will always involve products that are descendents of a single Customer Catalog node
		// This might be simpler than if we had to cascade out from multiple Parent nodes?
		//  
		// If we have notifications:
		$this->messages = $this->AvailableEntries->messages('array');
		if (empty($this->messages)) {
			return;
		}
		
		$this->EW = new EventWatchers();
		$this->EW->of($this->AvailableEntries->getCustomerUserId(), Observer::$allTypes);
		$this->data = $this->EW->collection();
		$this->data['Message'] = $this->messages;
		$fileWriter = new NotificationFileHandler();
		$fileWriter->writePending($this->data);
	}

}
