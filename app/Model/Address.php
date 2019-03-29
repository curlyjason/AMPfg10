<?php

App::uses('AppModel', 'Model');

/**
 * Address Model
 *
 * @property User $User
 * @property Customer $Customer
 */
class Address extends AppModel {

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'TaxRate' => array(
			'className' => 'TaxRate',
			'foreignKey' => 'tax_rate_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Customer' => array(
			'className' => 'Customer',
			'foreignKey' => 'customer_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	public $created = '';

/**
 *
 * @param type $id
 * @param type $table
 * @param type $ds
 */
	function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->virtualFields['csz'] = sprintf('CONCAT(%s.`city`, \', \', %s.`state`, \' \', %s.`zip`)', $this->alias, $this->alias, $this->alias);
	}

	public function getAddress($id) {
		$address = $this->find('first', array('conditions' => array('Address.id' => $id)));
//		$address['Address']['csz'] =
//				$address['Address']['city']
//				. ', '
//				. $address['Address']['state']
//				. ' '
//				. $address['Address']['zip'];
		return $address;
	}

/**
 * 
 * 
 * @param array $conditions Additional conditions
 * @return array The vendors
 */
	public function getVendors($conditions = array()) {
		$default = array('type' => 'vendor');
		if (!empty($conditions)) {
			$conditions = array_merge($conditions, $defaut);
		} else {
			$conditions = $default;
		}
		$raw = $this->find('all', array('conditions' => $conditions, 'recursive' => -1));
		$list = array();
		foreach ($raw as $index => $record) {
			$list[] = $record[$this->alias];
		}
		return $list;
	}

/**
 * 
 * 
 * @param array $conditions Additional conditions
 * @return array The vendors
 */
//	public function getVendorList() {
//		$conditions = array(
//			'type' => 'vendor'
//		);
//		return $this->find('list', array('conditions' => $conditions));
//	}

	function afterSave($created, $options = []) {
		parent::afterSave($created, $options);
		$this->created = $created;
	}

	public function fetchCustomerVendorList() {
		$accessibleCustomers = $this->Customer->getAccessibleCustomerInList();

		//perform find
		return $this->find('list', array(
			'fields' => array('id', 'name'),
			'conditions' => array(
				'customer_id' => $accessibleCustomers,
				'type' => 'vendor'
			)
		));
	}

}
