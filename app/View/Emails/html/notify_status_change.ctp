<?php
echo $this->Html->para('',"Order {$recordData['order_number']} status change to $status");
if ($loggedUser['access'] != 'Manager') {
	$why = 'your manager thinks your should receive';
	$manager = false;
} else {
	$why = 'your account settings indicate you want';
	$manager = true;
}
echo $this->Html->para('',"You are receiving this message because $why notification for {$user['name']} or {$customer['User']['username']}");
//if ($manager) {
//	echo $this->Html->para('', 'We plan to have a link here that will take you to your account page to change your settings');
//	echo $this->Html->para('', 'Or maybe a link to STOP notifications for in this case (not simple code beacuse both may be watched)');
//}
?>
