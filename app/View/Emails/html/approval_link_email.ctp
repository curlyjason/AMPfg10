<?php
//$this->FgHtml->ddd($overbudget, 'overbudget');
//$this->FgHtml->ddd(!$overbudget, '!overbudget');
//$this->FgHtml->ddd($inStock, 'inStock');
//$this->FgHtml->ddd($inStock === TRUE, 'inStock === TRUE');
//$this->FgHtml->ddd(!$inStock === TRUE, '!inStock === TRUE');
if (!$overbudget && $inStock === TRUE) {
	//<a href="/amp-fg/orders/statusChange/52d6d16d-26e0-4d1f-9870-038447139427/Approve">Approve</a>
	$approvalLink = $this->Html->link("Approve Order {$recordData['order_number']}", array(
		'controller' => 'gateways', 'action' => 'takeAction', $gateway_id, 'full_base' => true, 
		'?' =>array(
			'status' => 'Approve'
		))
	);
	echo $this->Html->para('', "Click this link to $approvalLink.");
} else {
	$reviewLink = $this->Html->link("Order {$recordData['order_number']}", array(
		'controller' => 'clients', 'action' => 'status', $recordData['id'], 'full_base' => true)
	);

	if ($overbudget) {
		echo $this->Html->para('', "There were budget overages on this order. Resolve before approval.");
	}
	if (is_string($inStock)) {
		echo $this->Html->para('', "$inStock.");
	}
	echo $this->Html->para('', "Use this link to review $reviewLink.");
	//http://localhost/amp-fg/clients/status/52d6d16d-26e0-4d1f-9870-038447139427

}

if ($approverCount > 1) {
	echo $this->Html->para('', "You are one of $approverCount approvers.");
} else {
	echo $this->Html->para('', "You are the only person who can approve this order");
}
?>
