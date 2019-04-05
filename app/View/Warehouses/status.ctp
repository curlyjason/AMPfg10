<?php
$this->start('css');
	echo $this->Html->css('ampfg_grain');
	echo $this->Html->css('status');
	echo $this->Html->css('cart');
	echo $this->Html->css('warehouse_status');
	echo $this->Html->css('document');
	echo $this->Html->css('ampfg_forms');
	echo $this->Html->css('location');
$this->end();

$this->start('script');
	echo $this->Html->script('grain');
	echo $this->Html->script('status');
	echo $this->Html->script('warehouse');
	echo $this->Html->script('invoice');
	echo $this->Html->script('shipment');
	echo $this->Html->script('shop_address');
	echo $this->Html->script('documents');
	echo $this->Html->script('location');
$this->end();

if (!isset($pullList)) {
    $pullList = array();
}

echo $this->Html->link('Collapse all', '#', array('id' => 'collapseAll'));

echo $this->Html->div('debug', '');

foreach ($pullList as $status => $orders) {
	//skip any statuses that have no orders in them
	if(empty($orders)){
		continue;
	}
	echo $this->element('released_grain', array(
		'data' => $orders,
		'class' => 'orderHeaderGrain',
		'group' => $status,
		'params' => array(),
//	'params' => array('group' => 'approved')
	));
}

if (!isset($replenishmentList)) {
    $replenishmentList = array();
}

foreach ($replenishmentList as $status => $replenishments) {
	//skip any statuses that have no replenishments in them
	if(empty($replenishments)){
		continue;
	}
	echo $this->element('replenishment_grain', array(
		'data' => $replenishments,
		'class' => 'replenishmentHeaderGrain',
		'group' => $status,
		'params' => array(),
//		'params' => array('group' => 'approved'),
		'alias' => 'Replenishment'
	));
}
?>
</div>