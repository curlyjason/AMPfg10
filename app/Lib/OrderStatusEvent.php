<?php
App::uses('CakeEventListener', 'Event');
App::uses('EventWatchers', 'Lib');
//App::uses('NotifyEmailLowInventory', 'Lib/Notifiers');
App::uses('NotificationFileHandler', 'Lib/Notifiers');
App::uses('Observer', 'Model');

/**
 * Description of LowInventoryEvent
 *
 * @author dondrake
 * @todo Is there a way to NOT resend notification on items? Or is that ok?
 *		 multiple notifications would show increasing need and most current replen requirements
 */
class OrderStatusEvent implements CakeEventListener {
	
//	/**
//	 * The Observation types and their triggers
//	 *
//	 * @var array
//	 */
//	private $observationTriggers = array(
//		'Approval' => array(
//			'Submitted'
//		),
//		'Notify' => array(
//			'Submitted',
//			'Approved',
//			'Shipped',
//			'Backordered'
//		)
//	);

	private $types = array(
		'Notify',
		'Approval'
	);
	
	private $orderArrayFilter = array(
		'id' => '',
		'order_number' => '',
		'status' => '',
		'first_name' => '',
		'last_name' => '',
		'user_id' => '',
		'order_item_count' => '',
		'total' => '',
		'user_customer_id' => '',
		'backorder_id' => '',
		'note' => '',
		'order_reference' => '',
		'billing_company' => ''
	);
	
	protected $message = array();
	
	public function implementedEvents() {
		return array(
//			'Order.Backorder' => 'backorder',
//			'Item.Pending' => 'replenishmentCheck',
			'Order.Status' => 'orderStatusNotification');
	}
	
	public function orderStatusNotification($event) {
		$this->message['order'] = array_intersect_key($event->subject['order'], $this->orderArrayFilter);
		$this->savePending($event->subject['customerUserId']);
		if ($event->subject['customerUserId'] != $event->subject['userId']) {
			$this->savePending($event->subject['userId']);
		}
	}
	
	private function savePending($id) {
		$this->Observer = ClassRegistry::init('Observer');
		$this->EW = new EventWatchers();
		$this->EW->of($id, $this->Observer->typesForStatusPending);
		$this->data = $this->EW->collection();
		$this->data['Message'] = $this->message;
		$fileWriter = new NotificationFileHandler();
		$fileWriter->writePending($this->data);
}
	

}
