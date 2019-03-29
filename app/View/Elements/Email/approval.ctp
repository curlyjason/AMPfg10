<?php
$data = $order->data();
//debug($data);
echo $this->Html->para('',"{$order->orderedFrom()}, Order {$data['order_number']} <b>needs approval</b>", array('style' => 'margin-top: 3; margin-bottom: 0; color: firebrick;'));

$budget = $order->getBudgetFor($order->userId());
echo $budget->budgetNotificationMessage($order->overItemLimits());