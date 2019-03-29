<?php
App::uses('AppModel', 'Model');
/**
 * Label Model
 *
 * @property Order $Order
 */
class Label extends AppModel {


	//The Associations below have been created with all possible keys, those that are not needed can be removed

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
	
	public function saveLabel($data) {
		$this->data = $data;
		$this->data['Label']['items'] = serialize($this->data['Label']['items']);
		$this->create($this->data);
		return $this->save();
	}
	
	public function fetchLabel($id) {
		$this->read(array('id', 'name', 'order_id', 'items'), $id);
		$this->data['Label']['items'] = unserialize($this->data['Label']['items']);
		return $this->data;
	}
	
	public function labelList($orderId) {
		return $this->find('list', array(
			'conditions' => array('order_id' => $orderId)
		));
	}
}
