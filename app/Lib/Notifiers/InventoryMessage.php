<?php

/**
 * Description of InventoryMessage
 *
 * @author jasont
 */
App::uses('MessageAbstract', 'Lib/Notifiers');

class InventoryMessage extends MessageAbstract {
	
	public function watchPoint($data = NULL) {
		if ($data !== NULL) {
			$this->watchPoint = "WP{$data['user_customer_id']}";
		}
		return $this->watchPoint;
	}
	
}
