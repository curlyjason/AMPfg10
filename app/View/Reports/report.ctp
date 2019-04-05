<?php 
$this->start('script');
	echo $this->Html->script('report');
$this->end();

echo $this->FgForm->create();
echo $this->FgForm->input('report', array(
	'type' => 'select',
	'empty' => 'Select a report',
	'options' => array('orders' => 'Order Activity', 'items' => 'Inventory Activity')
));
if (count($customers) > 1) {
	echo $this->FgForm->input('customers', array(
		'type' => 'select',
		'empty' => 'Select a customer',
		'options' => $customers
	));
} else {
	echo $this->FgForm->input('customers', array(
		'type' => 'hidden',
		'value' => key($customers)
	));
}
$lastMonth = date('m', time() - MONTH);
$year = date('Y', time() - MONTH);
echo $this->Html->tag('label', 'Enter a date range', array('for' => 'UserStartMonthMonth'));
echo $this->FgForm->month('start_month', array('empty' => 'Start Month', 'value' => $lastMonth));
echo $this->FgForm->year('start_year', 2013, date('Y',time()), array('empty' => 'Year', 'value' => $year));
echo '&nbsp;&nbsp;';
echo $this->FgForm->month('end_month', array('empty' => 'End Month', 'value' => $lastMonth));
echo $this->FgForm->year('end_year', 2013, date('Y',time()), array('empty' => 'Year', 'value' => $year));
//echo $this->FgForm->input('start_date');
//echo $this->FgForm->input('end_date');
echo $this->FgForm->submit('Make Report', array('bind' => 'click.reportValidation'));
echo $this->FgForm->end();

?>