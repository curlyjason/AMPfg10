<?php

App::uses('AppModel', 'Model');

/**
 * Price Model
 *
 * @property Customer $Customer
 */
class Price extends AppModel {

// <editor-fold defaultstate="collapsed" desc="Validation">
	public $validate = array(
		'max_qty' => array(
			'required' => array(
				'rule' => array('notBlank'),
				'message' => 'A maximum quantity is required'
			),
			'number' => array(
				'rule' => 'numeric',
				'message' => 'Max quantity must be a number'
			)
		),
		'min_qty' => array(
			'required' => array(
				'rule' => array('notBlank'),
				'message' => 'A minumum quantity is required'
			),
			'numeric' => array(
				'rule' => 'numeric',
				'message' => 'Min quantity must be a number'
			)
		),
		'price' => array(
			'required' => array(
				'rule' => array('notBlank'),
				'message' => 'A price is required'
			),
			'numeric' => array(
				'rule' => 'numeric',
				'message' => 'Price must be a number'
			)
		)
	);

// </editor-fold>
	
	public function fetchPullFee($customerId, $quantity) {
		$handling = $this->find('first', array(
			'conditions' => array(
				'Price.customer_id' => $customerId,
				'Price.test_max_qty >=' => $quantity
			),
			'order' => 'max_qty ASC'
		));

		$fee = (!empty($handling)) ? $handling['Price']['price'] : 0;
			
		return $fee;
	}

}
