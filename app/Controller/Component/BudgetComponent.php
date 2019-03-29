<?php

App::uses('Component', 'Controller');

/**
 * Budgets Component
 *
 * @property Budget $Budget
 */
class BudgetComponent extends Component {

	public function __construct(\ComponentCollection $collection, $settings = array()) {
		parent::__construct($collection, $settings);
		$this->Budget = ClassRegistry::init('Budget');
	}
	
	public function initialize(\Controller $controller) {
		parent::initialize($controller);
		$this->controller = $controller;
	}

	public function refreshBudget($id = null, $cart = TRUE) {
		$this->updateRemainingBudget($id, $cart);
		return $this->getRemainingBudget($id);
	}

		/**
     * Update the users current budget to reflect current expenditures
     * 
     * Recalculate and store the remaining budget figures for
     * users that use budgets. All others are ignored.
     * Optionally include cart. Cart is not included in the
     * expiring budget during monthly anniversary transitions
     * 
     * @param boolean $cart Should the cart be included in remaing budget calcs
     * @return void
     */
    public function updateRemainingBudget($id = null, $cart = true) {
	if ($id == null) {
        $id = $this->controller->Auth->user('budget_id');
        if ($id == null) {
            return;
        }
	}

    $order = $this->Budget->orderTotals($id);
    //remove cart items if handling orders connected to older budget months
    if(!$order['Budget']['current']){
        $cart = false;
    }
	if ($order['Budget']['id']) {
	    if ($order['Budget']['use_budget']) {
		$cart_total = $cart ? $this->controller->Session->read('Shop.Order.total') + 0 : 0;
		$order['Budget']['remaining_budget'] = $order['Budget']['budget'] - ($order['total'] + $cart_total);
	    }
	    if ($order['Budget']['use_item_budget']) {
		$cart_item_count = $cart ? $this->controller->Session->read('Shop.Order.order_item_count') + 0 : 0;
		$order['Budget']['remaining_item_budget'] = $order['Budget']['item_budget'] - ($order['item_count'] + $cart_item_count);
	    }
	    $this->Budget->save($order);
	}
    }
    
    /**
     * Read the current remaing budget values
     * 
     * @return array The budget values
     */
    public function getRemainingBudget($id=null) {
	$month = date('n/Y', time());
	if ($id == null) {
        $id = $this->controller->Auth->user('budget_id');
        if ($id == null) {
            return array('remaining_budget' => 'none', 'remaining_item_budget' => 'none', 'month' => $month);
        }
	}
	$budgetRecord = $this->Budget->find('first', array(
	    'conditions' => array('Budget.id' => $id),
	    'fields' => array('id', 'remaining_budget', 'remaining_item_budget', 'budget_month'),
	    'contain' => false
	));
	if (empty($budgetRecord)){
	    return array('remaining_budget' => 'not found', 'remaining_item_budget' => 'not found', 'month' => $month);
	}
	return $budgetRecord;
    }

}
