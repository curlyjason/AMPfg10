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
	echo $this->FgHtml->tag('h2', 'Edit ' . $this->request->data['Help']['name']);
	echo $this->FgHtml->div('helpEditBox', NULL);
		echo $this->FgForm->create();
		echo $this->FgForm->input('Help.id', array ('type' => 'hidden'));
		echo $this->FgForm->input('Help.tag', array ('type' => 'hidden'));
		echo $this->FgForm->input('Help.name');
		echo $this->FgForm->input('Help.help', array(
			'label' => 'Enter your markdown-flavored help text',
			'type' => 'textarea'
		));
		echo $this->FgForm->end();
	echo '</div>';//close helpEditBox div
	echo $this->FgHtml->div('helpClosingButtons');
		echo $this->FgForm->button('Save', array(
			'type' => 'submit',
			'class' => 'btn btn-default btn-primary helpSubmit',
			'bind' => 'click.submitHelp'
			));

		echo $this->FgForm->button('Cancel', array(
			'type' => 'button',
			'bind' => 'click.closeHelp',
			'class' => 'helpCancel'
		));
	echo '</div>';//close helpClosingButtons
echo '</div>';//close helpText div
?>