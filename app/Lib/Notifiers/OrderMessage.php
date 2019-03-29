<?php

/**
 * Description of OrderMessage
 *
 * @author jasont
 */
App::uses('MessageAbstract', 'Lib/Notifiers');
App::uses('Budget', 'Model');
App::uses('OrderItem', 'Model');

class OrderMessage extends MessageAbstract {
	
	private $Budget = FALSE;
	private $BudgetModel;
	private $user;
	private $UserModel;
	private $User;
	private $budgetRecord;
	
	public function __construct($data) {
		parent::__construct($data);
		$this->UserModel = new User();
		$this->UserModel->id = $this->userId();
		$this->user = $this->UserModel->find('first');
		unset($this->UserModel);
	}
	
	/**
	 * Return a single user's budget record
	 * 
	 * @param string $id the user id
	 * @return array
	 */
	public function getBudgetFor($id) {
		if ($this->Budget === FALSE) {
			$this->Budget = array();
			$this->BudgetModel = new Budget();
			$this->Budget[$id] = $this->BudgetModel->getCurrentBudgetFor($id);
			unset($this->BudgetModel);
		}
		return $this->Budget[$id];
	}
	
	public function overItemLimits() {
		$over = FALSE;
		
		$OrderItem = ClassRegistry::init('OrderItem');
		$this->items = $OrderItem->find('all', array(
			'conditions' => array(
				'OrderItem.order_id' => $this->data['id']
			),
			'fields' => array('id', 'quantity', 'name'),
			'contain' => array(
				'Catalog' => array(
					'fields' => array('max_quantity')
				)
			)
		));
		foreach ($this->items as $item) {
			if (!is_null($item['Catalog']['max_quantity'])) {
				$over = ($over || ($item['OrderItem']['quantity'] > $item['Catalog']['max_quantity']));
			}
		}

		return $over;
	}
	
	public function userId() {
		return $this->data['user_id'];
	}
	
	public function statusIs($status) {
		return strtolower($this->data['status']) === strtolower($status);
	}
	
	/**
	 * get the Customer name this was ordered from
	 * 
	 * @param string $field datum to return, id|name|null(returns name)
	 * @return string
	 */
	public function orderedFrom($field = NULL) {
		if ($field == 'id') {
			return $this->data['user_customer_id'];
		} 
		return $this->data['billing_company'];
	}
	
	/**
	 * Get the id or name of the user that placed the order
	 * 
	 * @param string $field datum to return, id|name|null(returns name)
	 * @return string
	 */
	public function orderedBy($field = NULL) {
		if ($field == 'id') {
			return $this->data['user_id'];
		} 
		return "{$this->data['first_name']} {$this->data['last_name']}";
	}
	
	public function watchPoints() {
		return array(
			'orderedBy' => $this->orderedBy('id'),
			'orderedFrom' => $this->orderedFrom('id')
		);
	}
}
