<?php
$this->start('css');
	echo $this->Html->css('ampfg_grain');
	echo $this->Html->css('status');
	echo $this->Html->css('invoice');
	echo $this->Html->css('cart');
	echo $this->Html->css('document');
	echo $this->Html->css('ampfg_forms');
$this->end();

$this->start('script');
	echo $this->Html->script('grain');
	echo $this->Html->script('order');
	echo $this->Html->script('status');
	echo $this->Html->script('help');
	echo $this->Html->script('invoice.js');
	echo $this->Html->script('shipment');
	echo $this->Html->script('shop_address');
	echo $this->Html->script('documents');
$this->end();

if(!isset($editGrain)){
    $editGrain = array();
}

if(!isset($watchedOrders)){
    $watchedOrders = array();
}

if (isset($editGrain)) {
	
	echo $this->Html->link('Collapse all', '#', array('id' => 'collapseAll'));

    echo $this->element('cart_header_grain');
    
    echo $this->element('approved_grain', array(
        'data' => $approvedOrders,
        'class' => 'orderHeaderGrain',
		'params' => array('group' => 'approved')
    ));
    
    echo $this->element('order_header_grain', array(
        'data' => $myOrders,
        'class' => 'orderHeaderGrain',
        'heading' => "Your %s Orders (<span class='count'>%s</span>)",
		'params' => array('group' => 'mine', 'approvable' => $approvable)
    ));
    
    echo $this->element('watched_grain', array(
        'data' => $watchedOrders,
        'class' => 'orderHeaderGrain',
		'params' => array('group' => 'watch', 'approvable' => $approvable)
    ));
    
    echo $this->element('order_header_grain', array(
        'data' => $connectedOrders,
        'class' => 'orderHeaderGrain',
        'namedHeading' => "%s %s Orders (<span class='count'>%s</span>)",
		'params' => array('group' => 'theirs', 'approvable' => $approvable)
    ));

    
    echo $this->element('approved_grain', array(
        'data' => $replenishments,
        'class' => 'orderHeaderGrain',
        'type' => "Replenishments: ",
		'params' => array('group' => 'replenishments', 'alias' => 'Replenishment')
    ));
}
?>
</div>