<?php
$this->start('css');
	echo $this->FgHtml->css('ampfg_grain');
	echo $this->FgHtml->css('status');
	echo $this->FgHtml->css('invoice');
	echo $this->FgHtml->css('cart');
	echo $this->FgHtml->css('document');
	echo $this->FgHtml->css('ampfg_forms');
$this->end();

$this->start('script');
	echo $this->FgHtml->script('grain');
	echo $this->FgHtml->script('order');
	echo $this->FgHtml->script('status');
	echo $this->FgHtml->script('help');
	echo $this->FgHtml->script('invoice.js');
	echo $this->FgHtml->script('shipment');
	echo $this->FgHtml->script('shop_address');
	echo $this->FgHtml->script('documents');
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