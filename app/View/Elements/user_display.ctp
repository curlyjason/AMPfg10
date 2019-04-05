<?php
echo $this->Session->flash();
$class = ($owner) ? 'user owner' : 'user';
//$this->FgHtml->ddd(array($group, $access), 'role');
if ($customerFlag && ($group === 'Staff' || $group === 'Admins') && $access === 'Manager') {
//	echo $this->FgForm->button('Invoice', array('class' => 'grainEdit', 'bind' => 'click.liveInvoice', 'customer' => $grain['User']['id']));
}
if ($access === 'Manager' || $owner) {
	echo $this->FgForm->editRequestButton(array('class' => $class));
}
echo $this->Html->div('userDisplay',null,array(
    'id' => $this->FgHtml->secureSelect($grain['User']['id'])
    ));
 if (!$customerFlag) {
		echo $this->FgHtml->decoratedTag('username', 'p', $grain['User']['username']);
		echo $this->FgHtml->decoratedTag('role', 'p', $grain['User']['role']);
		echo $this->FgHtml->decoratedTag('direct parent', 'p', $this->FgHtml->discoverName($grain['ParentUser']));
}
if ($customerFlag) {
	echo $this->FgHtml->decoratedTag('company type', 'p', $grain['Customer']['customer_type']);
	$primaryContact = $grain['Address'][0]['first_name'] . ' ' . $grain['Address'][0]['last_name'];
    echo $this->FgHtml->decoratedTag('primary contact', 'p', $primaryContact);
    echo $this->FgHtml->decoratedTag('email', 'p', $grain['Address'][0]['email']);
    echo $this->FgHtml->decoratedTag('phone', 'p', $grain['Address'][0]['phone']);
    echo $this->FgHtml->decoratedTag('token', 'p', $grain['Customer']['token']);
}
if ($grain['User']['use_budget']) {
    echo $this->FgHtml->decoratedTag('budget', 'p', $this->Number->currency($grain['User']['budget']));
    echo $this->FgHtml->decoratedTag('rollover monthly budget', 'p', ($grain['User']['rollover_budget']) ? 'Yes' : 'No');
}
if ($grain['User']['use_item_budget']) {
    echo $this->FgHtml->decoratedTag('item budget', 'p', $grain['User']['item_budget']);
    echo $this->FgHtml->decoratedTag('rollover monthly item budget', 'p', ($grain['User']['rollover_item_budget']) ? 'Yes' : 'No');
}
//if ($customerFlag && ($group === 'Staff' || $group === 'Admins') && $access === 'Manager' && !empty($invoices)) {
//	echo $this->FgHtml->decoratedTag('Past Invoices', 'p', $this->FgForm->input('past_invoices', array(
//		'options' => $invoices,
//		'label' => FALSE,
//		'div' => FALSE,
//		'empty' => 'Select an invoice to view',
//		'bind' => 'change.invoicePdf')));
//}
echo '</div>';
?>