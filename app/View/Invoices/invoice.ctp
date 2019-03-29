<?php 
echo $this->FgForm->create();
//echo $this->FgForm->input('report', array(
//	'type' => 'select',
//	'empty' => 'Select a report',
//	'options' => array('orders' => 'Order Activity', 'items' => 'Inventory Activity')
//));
//if (count($customers) > 1) {
	echo $this->FgForm->input('customers', array(
		'type' => 'select',
		'empty' => 'Select a customer',
		'options' => $customers
	));
//} else {
//	echo $this->FgForm->input('customers', array(
//		'type' => 'hidden',
//		'value' => key($customers)
//	));
//}
//echo $this->FgForm->input('start_date');
//echo $this->FgForm->input('end_date');
echo $this->FgForm->submit('Invoice');
echo $this->FgForm->end();

?>