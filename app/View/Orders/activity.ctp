<?php
echo $this->FgHtml->tag('h1', 'Order Activity Report <br/>' . $customerName . ': <br/>' . date('M d, Y', strtotime($start)) . ' - ' . date('M d, Y', strtotime($end)));
if (count($customers) > 1) {
	echo $this->FgForm->input('customers', array(
		'type' => 'select',
		'empty' => 'Select a customer',
		'options' => $customers,
		'bind' => 'change.differentCustomer'
	));
}

//	$this->FgHtml->ddd($data, 'data');
$this->start('css');
	echo $this->FgHtml->css('report');
$this->end();

$this->start('script');
	echo $this->FgHtml->script('report');
$this->end();

echo $this->Html->link(__('PDF'), array('controller' => 'orders', 'action' => 'activity', 'ext' => 'pdf', strtotime($start), strtotime($end), $customer), array('target' => '_blank'));

$c = count($data);

if($c==0){
	echo $this->FgHtml->tag('h2', "There are no orders for $customerName.");
} else {
	$key = key($data);
	$custIds = array_keys($data);
	unset($data[$key]['User']);
	foreach ($data[$key] as $status => $junk) {
		$s_it = new AppendIterator();
		$i=0;
		while($i<$c){
			$s_it->append(new ArrayIterator($data[$custIds[$i++]][$status]));
		}
		echo $this->Report->orderReportBlock($status, $s_it);
	}
}