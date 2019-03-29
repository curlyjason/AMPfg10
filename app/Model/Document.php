<?php
App::uses('BaseDoc', 'Model');
/**
 * Document Model
 *
 * @property Order $Order
 */
class Document extends BaseDoc {

	/**
     * belongsTo associations
     *
     * @var array
     */
    public $belongsTo = array(
		'Order' => array(
			'className' => 'Order',
			'foreignKey' => 'order_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
    );

	/**
	 * Convert doc table from cart connection to order connection
	 * 
	 * @param string $order_id The id of the order to connect to
	 */
	public function order($user_id, $order_id) {
		$cartDocs = $this->find('all', array(
			'recursive' => -1,
			'conditions' => array(
				'Document.user_id' => $user_id
			)
		));
		foreach ($cartDocs as $index => $record) {
			$record['Document']['user_id'] = '';
			$record['Document']['order_id'] = $order_id;
		}
		$this->save($cartDocs);
	}
	
	public function moveDocs($orderId, $userId) {
	    //Specifically cast the $userId variable as an integer to conform with db standards
        $iUserId = (int)$userId;
        if(!$this->query("UPDATE documents SET order_id = '$orderId', user_id = NULL WHERE user_id = $iUserId")){
			CakeLog::error("order: $orderId; user: $userId did not move documents");
		}
	}

}
