<?php

echo $this->Html->div('userPermissionForm', NULL);
echo $this->Flash->render();
echo $this->FgForm->create('User', array('class' => 'grainVersion'));
echo $this->FgForm->input('User.id', array('type' => 'hidden'));

$keys = array_flip($user_selected);

echo '<div class="ajaxPull ajaxEditPull" id="User">';
		$this->FgHtml->setSelected($user_selected);// insure current access is checked
		echo '<p id="UserAccess" class="toggle">User Group Access</p>';
		echo '<div class="UserAccess access">';
			echo $this->FgHtml->recursiveTree('checkbox', $accessibleUsers, $this->Session->read('Auth.User.UserRoots'));
		echo '</div>';
echo '</div>'; // close the catalog checkbox wrapper div

echo $this->FgForm->button('Cancel', array(
    'type' => 'button',
    'bind' => 'click.basicCancelButton'
));
echo $this->FgForm->button('Submit', array(
    'type' => 'submit',
    'class' => 'submitButton'
));
echo $this->FgForm->end();
echo '</div>' //close the userPermissionForm div
?>