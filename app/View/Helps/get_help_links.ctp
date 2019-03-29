<?php

// Arriving with $help
//array(
//	(int) 0 => array(
//		'Help' => array(
//			'tag' => 'method',
//			'name' => 'NEW HELP: method'
//		)
//	),
//	(int) 1 => array(
//		'Help' => array(
//			'tag' => 'total',
//			'name' => 'NEW HELP: total'
//		)
//	),
//	(int) 2 => array(
//		'Help' => array(
//			'tag' => 'sample1',
//			'name' => 'Sample Help'
//		)
//	)
//)


// Setup variables
$role = $this->Session->read('Auth.User.role');


foreach ($help as $key => $record) {
	//create edit link
	if (/*$role == 'Admins Manager'*/true) {
		$editLink = $this->FgHtml->link('(Edit)', array('controller' => 'helps', 'action' => 'editHelp', $record['Help']['tag']), array('bind' => 'click.editHelp'));
	}	else {
		$editLink = '';
	}
	if ($role == 'Admins Manager' && preg_match('/^zz/', $record['Help']['name'])) {
		//new help link
		echo $this->FgHtml->para('', $this->FgHtml->link($record['Help']['name'], array('controller' => 'helps', 'action' => 'displayHelp', $record['Help']['tag']), array('bind' => 'click.displayHelp')).$editLink);							
	} elseif(!preg_match('/^zz/', $record['Help']['name'])) {
		//existing help link
		echo $this->FgHtml->para('', $this->FgHtml->link($record['Help']['name'], array('controller' => 'helps', 'action' => 'displayHelp', $record['Help']['tag']), array('bind' => 'click.displayHelp')).$editLink);		
	}
}
?>