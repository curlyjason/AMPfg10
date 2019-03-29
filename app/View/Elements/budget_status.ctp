<?php
$budgetId = $this->Session->read('Auth.User.budget_id');
if ($budgetId) {
//	$this->installComponent('Budget');
//	$budget = $this->Budget->getRemainingBudget();
//	$budget = $this->requestAction(array('controller' => 'budgets', 'action' => 'getRemainingBudget'));

echo '<div id="budget_status" class="budgetstatus-'. $budgetId .'">';
    if ($this->Session->read('Auth.User.use_budget')) {
		$over = stristr($budget['Budget']['remaining_budget'], '-');
		$overClass = $over ? ' negative' : '';
		$overLabel = $over ? 'Over ' : '';

		echo "<div id='budget' class='budget-$budgetId $overClass' >";
			echo $this->FgHtml->decoratedTag($overLabel . 'Budget', 'p', $budget['Budget']['remaining_budget']);
		echo '</div>';
    }
    
    if ($this->Session->read('Auth.User.use_item_budget')) {
		$over = stristr($budget['Budget']['remaining_item_budget'], '-');
		$overClass = $over ? ' negative' : '';
		$overLabel = $over ? 'Over ' : '';

		echo "<div id='item_budget' class='itembudget-$budgetId $overClass' >";
		echo $this->FgHtml->decoratedTag($overLabel . 'Item Budget', 'p', $budget['Budget']['remaining_item_budget']);
		echo '</div>';
    }
echo '</div>';
}
?>
