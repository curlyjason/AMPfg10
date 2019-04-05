<h1>
<?php
	echo 'Inventory Transaction Report <br/>' . $customerName . ': <br/>' . date('M d, Y', $report['firstTime']) . ' - ' . date('M d, Y', $report['finalTime'])
?>
</h1>
<?php

echo $this->Html->link(__('PDF'), array('controller' => 'items', 'action' => 'activity', 'ext' => 'pdf', strtotime($start), strtotime($end), $customer), array('target' => '_blank'));

if (count($customers) > 1) {
	echo $this->FgForm->input('customers', array(
		'type' => 'select',
		'empty' => 'Select a customer',
		'options' => $customers,
		'bind' => 'change.differentCustomer'
	));
}

$this->start('css');
	echo $this->Html->css('report');
$this->end();

$this->start('script');
	echo $this->Html->script('report');
$this->end();

echo $this->element('Item/activity');