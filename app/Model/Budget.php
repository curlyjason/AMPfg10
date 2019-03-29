<?php

App::uses('AppModel', 'Model');
App::uses('BudgetObj', 'Lib');

/**
 * Budget Model
 *
 * @property User $User
 */
class Budget extends AppModel {

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = array(
	'user_id' => array(
	    'numeric' => array(
		'rule' => array('numeric'),
	    //'message' => 'Your custom message here',
	    //'allowEmpty' => false,
	    //'required' => false,
	    //'last' => false, // Stop validation after this rule
	    //'on' => 'create', // Limit validation to 'create' or 'update' operations
	    ),
	),
	'use_budget' => array(
	    'boolean' => array(
		'rule' => array('boolean'),
	    //'message' => 'Your custom message here',
	    //'allowEmpty' => false,
	    //'required' => false,
	    //'last' => false, // Stop validation after this rule
	    //'on' => 'create', // Limit validation to 'create' or 'update' operations
	    ),
	),
	'use_item_budget' => array(
	    'boolean' => array(
		'rule' => array('boolean'),
	    //'message' => 'Your custom message here',
	    //'allowEmpty' => false,
	    //'required' => false,
	    //'last' => false, // Stop validation after this rule
	    //'on' => 'create', // Limit validation to 'create' or 'update' operations
	    ),
	),
	'current' => array(
	    'boolean' => array(
		'rule' => array('boolean'),
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
	'User' => array(
	    'className' => 'User',
	    'foreignKey' => 'user_id',
	    'conditions' => '',
	    'fields' => '',
	    'order' => ''
	)
    );


    /**
     * hasMany associations
     *
     * @var array
     */
    public $hasMany = array(
        'Order' => array(
            'className' => 'Order',
            'foreignKey' => 'budget_id',
            'dependent' => false,
            'conditions' => '',
            'fields' => '',
            'order' => '',
            'limit' => '',
            'offset' => '',
            'exclusive' => '',
            'finderQuery' => '',
            'counterQuery' => ''
        )
    );
    
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
	}

	/**
     * Make a new monthly budget if necessary
     * 
     * At login, determine if the User has a budget
     * and if we've entered a new month
     * If so, make a new budget 
     * 
     * @param array $user The Auth->user() array
     * @return void
     */
    public function setBudget($user) {

	// User does not use budgets at all
	if ((!isset($user['use_budget']) && !isset($user['use_item_budget'])) || (!$user['use_budget'] && !$user['use_item_budget'])) {
	    return;
	}

	// User does use some budget. Let's see what their current month looks like
	$date = date('n/Y', time());
	$budget = $this->User->find('first', array(
	    'conditions' => array('User.id' => $user['id']),
	    'contain' => 'Budget'));
	// User has a budget for this month. No setup needed
	if (isset($budget['Budget'][0]['budget_month']) && $budget['Budget'][0]['budget_month'] == $date) {
	    return;
	}

	// if there is an expired budget, turn it off
	if (isset($budget['Budget'][0]['budget_month'])) {
	    $newBudget[0]['Budget']['id'] = $budget['Budget'][0]['id'];
	    $newBudget[0]['Budget']['current'] = 0;
	}
	
	// User needs a budget for this month
	$newBudget[1]['Budget']['user_id'] = $budget['User']['id'];
	$newBudget[1]['Budget']['budget_month'] = $date;
	$newBudget[1]['Budget']['current'] = 1;

	// dollar budget
	// recalculate the remaining budget excluding any open cart
	$newBudget[1]['Budget']['use_budget'] = $budget['User']['use_budget'];
	if ($budget['User']['rollover_budget'] && isset($budget['Budget'][0]['remaining_budget'])) {
	    $newBudget[1]['Budget']['budget'] = $budget['User']['budget'] + $budget['Budget'][0]['remaining_budget'];
	} else {
	    $newBudget[1]['Budget']['budget'] = $budget['User']['budget'];
	}
	$newBudget[1]['Budget']['remaining_budget'] = $newBudget[1]['Budget']['budget'];

	// item budget
	// recalculate the remaining budget excluding any open cart
	$newBudget[1]['Budget']['use_item_budget'] = $budget['User']['use_item_budget'];
	if ($budget['User']['rollover_item_budget'] && isset($budget['Budget'][0]['remaining_item_budget'])) {
	    $newBudget[1]['Budget']['item_budget'] = $budget['User']['item_budget'] + $budget['Budget'][0]['remaining_item_budget'];
	} else {
	    $newBudget[1]['Budget']['item_budget'] = $budget['User']['item_budget'];
	}
	$newBudget[1]['Budget']['remaining_item_budget'] = $newBudget[1]['Budget']['item_budget'];

	$this->saveAll($newBudget);
    }

    /**
     * Get the id of the user's current budget
     * 
     * @param int $userId Id of the user
     * @return mixed id of the user's current budget record of FALSE
     */
    public function getBudgetId($userId) {
	return $this->field('id', array('user_id' => $userId, 'current' => 1));
    }
	
	/**
	 * Get the BudgetObj data object for the user
	 * 
	 * @param string $id the User id
	 * @return array The budget array and its child User array
	 */
	public function getCurrentBudgetFor($id) {
		$budgetRecord = $this->find('first', array(
			'conditions' => array('Budget.user_id' => $id, 'current' => 1),
	//	    'fields' => array('id', 'remaining_budget', 'remaining_item_budget', 'budget_month'),
			'contain' => array('User')
		));
		return new BudgetObj($budgetRecord);
	}
    
    /**
     * Get the dollar and item totals for the current orders
     * 
     * For a single budget, past or present
     * 
     * @param type $id budget id
     * @return array elements 'total' and 'item_total
     */
    public function orderTotals($id) {
//	'Order' => array(
//		0 => array(
//			'budget_id' => '7',
//			'Order' => array(
//				0 => array(
//					'SUM(total)' => '136.50',
//					'SUM(order_item_count)' => '6'
	$budget = $this->find('first', array(
	    'conditions' => array(
		'Budget.id' => $id),
	    'fields' => array(
		'id',
		'user_id',
		'use_budget',
		'budget',
		'use_item_budget',
		'item_budget',
            'current',
            'budget_month'
	    ),
	    'contain' => array(
		'Order' => array(
		    'fields' => array(
			'SUM(subtotal)',
			'SUM(order_item_count)'
		    )
		)
	    )
	));
	$order = array('Budget' => false, 'total' => 0, 'item_count' => 0);

	$order['Budget'] = isset($budget['Budget']['id']) ? $budget['Budget'] : false;
	if (isset($budget['Order'][0]['Order'][0]['SUM(subtotal)'])) {
	    $order['total'] = $budget['Order'][0]['Order'][0]['SUM(subtotal)'];
	}
	if (isset($budget['Order'][0]['Order'][0]['SUM(order_item_count)'])) {
	    $order['item_count'] = $budget['Order'][0]['Order'][0]['SUM(order_item_count)'];
	}
//	'id' => '7',
//		'user_id' => '15',
//		'use_budget' => true,
//		'budget' => '120',
//		'remaining_budget' => '120',
//		'use_item_budget' => true,
//		'item_budget' => '13',
//		'remaining_item_budget' => '13',
//		'budget_month' => '11/2013',
//		'current' => true,
//		'created' => '2013-11-14 16:26:24',
//		'modified' => '2013-11-14 16:26:24'
	return $order;
    }
}
