<?php
foreach ($data as $user => $statuses) {
//    $dataUser = $statuses['User'];
//    unset($statuses['User']);
//    $isFirst = 'first'; //a css hook to tighten grouping for a single user
//
//    foreach ($statuses as $status => $orders) {
//        $displayStatus = Inflector::classify($status);
//        $displayName = $this->FgHtml->discoverName($dataUser) . '\'s';
//	$count = count($orders);
//        if (isset($heading)) {
//            $displayHeading = sprintf($heading, $displayStatus, $count);
//        } else {
//            $displayHeading = sprintf($namedHeading, $displayName, $displayStatus, $count);
//        }
//	echo '<div>';
//        echo $this->Html->tag('h3', sprintf($displayHeading, $displayStatus), array(
//            'id' => $status . $user,
//            'class' => 'grainDisplay toggle ' . $isFirst
//        ));
//        foreach ($orders as $index => $order) {
//            echo $this->FgHtml->orderWrapper($order, $params, $index);
//        }
//	echo '</div>';
//	$isFirst = 'notFirst'; //a css hook to tighten grouping for a single user
//	// done with all the orders at this status level. Loop for next status 
//    }
    // done with this status level for one user. Loop for next user
}
?>