<?php 
$this->start('css');
	echo $this->Html->css('search');
	echo $this->Html->css('status');
	echo $this->Html->css('shopping');
	echo $this->FgHtml->css('invoice');
	echo $this->FgHtml->css('cart');
	echo $this->FgHtml->css('document');
	echo $this->FgHtml->css('ampfg_forms');

$this->end();

$this->start('script');
	echo $this->Html->script('grain');
	echo $this->Html->script('order');
	echo $this->Html->script('search');
	echo $this->Html->script('status');
	echo $this->Html->script('invoice');
	echo $this->Html->script('addToCart');
	echo $this->FgHtml->script('shipment');
	echo $this->FgHtml->script('shop_address');
	echo $this->FgHtml->script('documents');
	
$this->end();

echo $this->FgForm->create('User', array('class' => 'help', 'help' => 'Search'));
echo $this->FgForm->input('search');

	echo $this->Html->div('tools', null);
	echo $this->FgHtml->para('toggle', 'Advanced Search', array('id' => 'filter'));
	echo $this->Form->input('filter', array(
		'options' => $filters,
		'type' => 'select',
		'multiple' => 'checkbox',
		'label' => false,
		'selected' => $defaultFilters,
		'div' => array('class' => 'filter hide'))); // classes for ajax form toggle hooks
//	echo $this->FgHtml->para('filter hide', 
//			$this->Html->link('Save these settings as my default', array(
//				'action' => 'searchFilterPreference'),
//				array('bind' => 'click.searchPreference')));
	echo '</div>';

echo $this->FgForm->end('submit');

//	$this->FgHtml->ddd($users, 'userQuery');
//	$this->FgHtml->ddd($customers, 'customerQuery');
//	$this->FgHtml->ddd($orders, 'orderQuery');


if (!empty($users)) {
	echo $this->Html->div('search users', $this->Html->tag('h3', 'Users and Customers'));
	foreach ($users as $user) {
		echo $this->Search->foundUser($user, $query);
	}
	foreach ($customers as $customer) {
		echo $this->Search->foundCustomer($customer, $query);
	}
}

if (!empty($orders)) {
	echo $this->Html->div('search orders', $this->Html->tag('h3', 'Orders'));
		echo $this->element('watched_grain', array(
			'data' => $orders,
			'class' => 'orderHeaderGrain',
			'prefix' => '',
			'params' => array('group' => 'watch', 'approvable' => $approvable)
		));
}
if (!empty($replenishments)) {
	echo $this->element('approved_grain', array(
		'data' => $replenishments,
		'class' => 'orderHeaderGrain',
		'type' => "Replenishments: ",
		'params' => array('group' => 'replenishments', 'alias' => 'Replenishment')
	));
}
if (!empty($catalogs)) {
	echo $this->Html->div('search catalogs', $this->Html->tag('h3', 'Catalog Items'));
	foreach ($catalogs as $index => $entry) {
		echo $this->element('store_grain', array(
			'entry' => $entry,
			'query' => $query
		));
	}
}

//$this->FgHtml->ddd($users, 'Users');
//$this->FgHtml->ddd($customers, 'Customers');
?>