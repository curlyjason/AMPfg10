<?php
$data = $order->data();
echo $this->Html->para('',"{$order->orderedFrom()}, Order {$data['order_number']} status change to {$data['status']}", array('style' => 'margin-top: 3; margin-bottom: 0;'));

$budget = $order->getBudgetFor($order->userId());
echo $budget->budgetNotificationMessage($order->overItemLimits());