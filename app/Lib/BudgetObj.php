<?php
/**
 * Description of BudgetObj
 *
 * @author dondrake
 */
class BudgetObj {
	
	private $user;
	private $budget;


	/**
	 * Build the object from a typical Budget query
	 * 
	 * array('Budget' => array(
	 *		fields, 
	 *		'User' => array(
	 *			fields
	 *		)
	 * )
	 * 
	 * @param type $budget_user Budget model query array with User data
	 */
	public function __construct($budget_user = NULL) {
		if (isset($budget_user['User'])) {
			$this->user = $budget_user['User'];
			unset($budget_user['User']);
		}
		
		if (isset($budget_user['Budget'])) {
			$this->budget = $budget_user['Budget'];
		}
	}

	public function budgetNotificationMessage($overItemLimit = FALSE) {
		$message = '';
		
		$warn = ($this->usesBudget() && $this->overBudget())
				|| ($this->usesItemBudget() && $this->overItemBudget())
				|| ($this->usesItemLimitBudget() && $overItemLimit);
		
		if ($warn) {
			$message = "<p style=\"margin-top: 0; margin-left: 6px;\"\">{$this->userName()} has exceeded their budget on this order.<br />";
			if ($this->usesBudget() && $this->overBudget()) {
				$message .= "<span style=\"font-size: 90%; margin-left: 6px;\"> - Current available budget \${$this->remainingBudget()}.</span>";
				if ( ($this->usesItemBudget() && $this->overItemBudget()) || ($this->usesItemLimitBudget() && $overItemLimit) ) {
					$message .= "<br />";
				}
			}
			if ($this->usesItemBudget() && $this->overItemBudget()) {
				$message .= "<span style=\"font-size: 90%; margin-left: 6px;\"> - Current available item-count budget is {$this->remainingItemBudget()}.</span>";
				if ( ($this->usesItemLimitBudget() && $overItemLimit) ) {
					$message .= "<br />";
				}
			}
			if ($this->usesItemLimitBudget() && $overItemLimit) {
				$message .= "<span style=\"font-size: 90%; margin-left: 6px;\"> - At least one item has a quantity beyond the specified limit for the product.</span>";
			}
			$message .= '</p>';
		}
		
		return $message;
	}
	
	public function usesBudget() {
		return $this->user['use_budget'];
	}
	
	public function overBudget() {
		return $this->budget['remaining_budget'] < 0 ;
	}
	
	public function userName() {
		return $this->user['first_name'] . ' ' . $this->user['last_name'];
	}
	
	public function remainingBudget() {
		return $this->budget['remaining_budget'];
	}
	
	public function usesItemBudget() {
		return $this->user['use_item_budget'];
	}

	public function overItemBudget() {
		return $this->budget['remaining_item_budget'] < 0 ;
	}

	public function remainingItemBudget() {
		return $this->budget['remaining_item_budget'];
	}

	public function usesItemLimitBudget() {
		return $this->user['use_item_limit_budget'];
	}

	}
