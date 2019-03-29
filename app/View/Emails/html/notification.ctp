<?php

/*
 * Copyright 2015 Origami Structures
 */

App::uses('User', 'Model');
//Basic variable setups
$types = array('Approval', 'Notify', 'XmlLowInventory');
$completed = array();

//Setup blocks so that we can control order
//$this->Notice->startNoticeBlock('approval');
$this->start('approval');
$this->end();
//$this->Notice->startNoticeBlock('notify');
$this->start('notify');
$this->end();
//$this->Notice->startNoticeBlock('low inventory');
$this->start('low inventory');
$this->end();

foreach ($types as $type) {
	if (isset($messages[$type])) {
		foreach ($messages[$type] as $watch_point_name => $watch_point_messages) {
			echo $this->Html->tag('h1', "$type for $watch_point_name");
			foreach ($watch_point_messages as $key => $Message) {
				switch (array_keys($Message->message)[0]) {
					case 'LowInventory':
						foreach ($Message->message['LowInventory'] as $key => $data) {
							if (!in_array($key, $completed)) {
								$notification = new Available();
								$notification->data($data);
								$this->append('low inventory');
								echo $this->element('Email/lowInventory', array('data' => $notification));
								$this->end();
								$completed[$key] = $key;
							}
						}
						break;

					case 'order':
						$notification = new OrderMessage($Message->message['order']);
						if ($type === 'Approval' && $notification->statusIs('Submitted') && !in_array($Message->message['order']['order_number'], $completed)) {
							$this->append('approval');
							echo $this->element('Email/approval', array('order' => $notification));
							$this->end();
							$completed[$Message->message['order']['order_number']] = $Message->message['order']['order_number'];
						} else if (!in_array($Message->message['order']['order_number'], $completed)) {
							$this->append('notify');
							echo $this->element('Email/submitted', array('order' => $notification));
							$this->end();
							$completed[$Message->message['order']['order_number']] = $Message->message['order']['order_number'];
						}
						break;

					default:
						break;
				}
			}
			foreach ($this->blocks() as $block) {
				echo $this->fetch($block);
				$this->assign($block, '');
			}
			
		}		
	}
}
//exit;