<?php

// Arriving with $helpText
//array(
//	'Help' => array(
//		'id' => '3',
//		'created' => null,
//		'modified' => null,
//		'name' => 'Sample Help',
//		'help' => 'This is a sample help document. It has a list of points the user should know.
//
//- Point 1
//- Point 2',
//		'tag' => 'sample1'
//	)
//)

// Setup variables
$role = $this->Session->read('Auth.User.role');

echo $this->FgHtml->div('helpText float', NULL, array('id' => 'helpText'));//open help window div
	echo $this->FgHtml->para('close', 'X', array('bind' => 'click.closeHelp'));
	echo $this->FgHtml->tag('h2', $helpText['Help']['name']);
	echo $this->FgHtml->div('helpTextMarkdown', NULL);
		echo $this->FgHtml->markdown($helpText['Help']['help']);
	echo '</div>';//close helpTextMarkdown div
	if ($role == 'Admins Manager') {
		echo $this->FgHtml->link('Edit this help', array('controller' => 'helps', 'action' => 'editHelp', $helpText['Help']['tag']), array('bind' => 'click.editHelp'));
	}
echo '</div>';//close helpText div
?>