<?php

App::uses('AppModel', 'Model');
App::uses('CustomerEntity', 'Model/Entity');

/**
 * Customer Model
 *
 * @property User $User
 * @property Address $Address
 */
class Customer extends AppModel {
// <editor-fold defaultstate="collapsed" desc="Associations">
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
		'Address' => array(
			'className' => 'Address',
			'foreignKey' => 'address_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);
	
    public $hasMany = array(
		'Price' => array(
			'className' => 'Price',
			'foreignKey' => 'customer_id',
			'dependent' => true
		),
		'Invoice' => array(
			'className' => 'Invoice',
			'foreignKey' => 'customer_id'
		),
		'InvoiceItem' => array(
			'className' => 'InvoiceItem',
			'foreignKey' => 'customer_id'
		)
	); 
	
	public $hasOne = array(
		'Catalog' => array(
			'className' => 'Catalog',
			'foreignKey' => 'customer_id'
		),
		'Vendor' => array(
			'className' => 'Address',
			'foreignKey' => 'customer_id'
		)
	);
// </editor-fold>
	
// <editor-fold defaultstate="collapsed" desc="Validation">
	public $validate = array(
		'customer_code' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'An EPMS customer ID is required'
			)
		),
		'user_id' => array(
			'unique' => array(
				'rule' => array('isUnique'),
				'message' => 'There can only be one customer related to any user'
			)
		)
	); 
// </editor-fold>
	
// <editor-fold defaultstate="collapsed" desc="Properties">
	public $virtualFields = array(
		'name' => 'User.username',
		'role' => 'User.role'
	);
	
	public $customerQuery = array();
	
	public $query = '';
	
	public $accessibleUserInList = array(); 
	
	public $customer_type = array(
		'AMP' => 'AMP',
		'GM' => 'GM'
	);
	
// </editor-fold>


	/**
	 * Search the Customer contact fields
	 * 
	 * Within the set of accesible Users,
	 * find Customers where contact names contain the search string
	 * 
	 * Customers are just meta-data for Users
	 * so discoveries are treated as Users by the search tool
	 * when they are output. User fields are returned to
	 * support the search page helpers
	 * 
	 * @param string $query The search string
	 * @return array The found records
	 */
	public function queryCustomers($query) {
		if ( !$this->User->accessibleUserInList ){
			$this->User->getAccessibleUserInList();
		}
		
		//perform find
		$this->customerQuery = $this->find('all', array(
			'conditions' => array(
				'Customer.user_id' => $this->User->accessibleUserInList,
				'OR' => array(
					'Customer.order_contact LIKE' => "%{$query}%",
					'Customer.billing_contact LIKE' => "%{$query}%"
				)
			),
			'fields' => array(
				'user_id', 'order_contact', 'billing_contact'
			),
			'contain' => array(
				'User' => array(
					'fields' => array(
						'id', 'first_name', 'last_name', 'username'
					),
						'Order' => array(
							'fields' => array('Order.id')
						),
						'Replenishment' => array(
							'fields' => array('Replenishment.id')
						)
					)
			)
		));
		return $this->customerQuery;
	}
	
	/**
	 * Return the storage fees for this customer
	 * 
	 * Formatted as an InvoiceItem record array for addition to the invoice
	 * 
	 * @param string $id, the customer's user id
	 * @return array
	 */
	public function fetchStorageCharges($id) {
		$storageArray = array();
		$customer = $this->find('first', array(
			'conditions' => array(
				'Customer.user_id' => $id
			)
		));
		if (!empty($customer)) {
			$charge = $customer['Customer']['rent_qty'] * $customer['Customer']['rent_price'];
			$storageArray = array(
				'name' => 'Storage',
				'customer_id' => $id,
				'quantity' => $customer['Customer']['rent_qty'],
				'unit' => $customer['Customer']['rent_unit'],
				'price' => $customer['Customer']['rent_price'],
				'description' => 'Storage Fees'
			);
		}		
		return $storageArray;
	}
	
	/**
	 * Return a complete customer array with user and address data
	 * Based upon the customer's user id
	 * 
	 * @param string $id the customer's user id
	 * @return array
	 */
	public function fetchCustomer($id) {
		$customer = $this->find('first', array(
			'conditions' => array(
				'Customer.user_id' => $id
			),
			'contain' => array(
				'User', 'Address'
			)
		));
		return $customer;
	}
	
	/**
	 * Given a user id, find the customer id
	 * 
	 * @param string $userId
	 * @return string|boolean the customer id or FALSE
	 */
	public function getIdThroughUserId($userId) {
		return $this->field($this->primaryKey, array('user_id' => $userId));
	}
	
	/**
	 * Return a list of accessible customers
	 * 
	 * @return inList
	 */
	public function getAccessibleCustomerInList() {
		if ( !$this->User->accessibleUserInList ){
			$this->User->getAccessibleUserInList();
		}
		$list = $this->find('list', array(
			'fields' => array('id', 'id'),
			'conditions' => array(
				'user_id' => $this->User->accessibleUserInList
			)
		));
        $inactive = $this->User->find('list', array(
            'fields' => array('User.id', 'User.id'),
            'conditions' => array(
                'User.active' => 0
            )
        ));
        return array_diff_key($list, $inactive);
	}
	
}
