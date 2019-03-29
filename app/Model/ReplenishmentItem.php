<?php
App::uses('AppModel', 'Model');
/**
 * ReplenishmentItem Model
 *
 * @property Replenishment $Replenishment
 * @property Item $Item
 */
class ReplenishmentItem extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'replenishment_id' => array(
			'uuid' => array(
				'rule' => array('uuid'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'name' => array(
			'notBlank' => array(
				'rule' => array('notBlank'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'quantity' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Replenishment' => array(
			'className' => 'Replenishment',
			'foreignKey' => 'replenishment_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Item' => array(
			'className' => 'Item',
			'foreignKey' => 'item_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
	public function pullOrderNumber($replenishmentItemId) {
		$replenishmentId = $this->field('replenishment_id', array('id' => $replenishmentItemId));
		$orderNumber = $this->Replenishment->field('order_number', array('id' => $replenishmentId));
		return $orderNumber;
	}

}
