<?php

//debug($data);die;
$prefix = (!isset($prefix)) ? 'Watched: ' : $prefix;
	
$isFirst = 'first'; //a css hook to tighten grouping for a single user
$g = 221;
$r = 221;
foreach ($data as $status => $orders) {
	if ($status == 'Approved' || empty($orders)) {
		continue;
	}
//    $dataUser = $statuses['User'];
//    unset($statuses['User']);
//    foreach ($statuses as $status => $orders) {
    $displayStatus = Inflector::classify($status);
    $count = count($orders);
    echo '<div>';
    echo $this->Html->tag('h3', "$prefix $status (<span class='count'>$count</span>)", array(
        'id' => "Watch$status",
        'class' => 'grainDisplay toggle ' . $isFirst,
		'style' => "background-color:rgb($r,221,$g)"
    ));
	$g -= 8;
	$r -= 4;
    $index = 0;
    foreach ($orders as $order) {
        echo $this->Status->orderWrapper($order, $params, $index++, "Watch$status");
    }
    echo '</div>';
	$isFirst = 'notFirst'; //a css hook to tighten grouping for a single user
	// done with all the orders at this status level. Loop for next status 
}
?>