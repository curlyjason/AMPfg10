<?php
$this->start('css');
	echo $this->FgHtml->css('ampfg_grain');
	echo $this->FgHtml->css('status');
	echo $this->FgHtml->css('cart');
	echo $this->FgHtml->css('warehouse_status');
	echo $this->FgHtml->css('document');
	echo $this->FgHtml->css('ampfg_forms');
	echo $this->FgHtml->css('location');
$this->end();

$this->start('script');
	echo $this->FgHtml->script('grain');
	echo $this->FgHtml->script('status');
	echo $this->FgHtml->script('warehouse');
	echo $this->FgHtml->script('invoice');
	echo $this->FgHtml->script('shipment');
	echo $this->FgHtml->script('shop_address');
	echo $this->FgHtml->script('documents');
	echo $this->FgHtml->script('location');
$this->end();

if (!isset($pullList)) {
    $pullList = array();
}

echo $this->FgHtml->link('Collapse all', '#', array('id' => 'collapseAll'));

echo $this->FgHtml->div('debug', '');

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