<?php
// Drop lists act differently for edit and add forms
if (isset($this->request->data['Observer']['id'])) {
    $emptyObserver = false;
    $emptyType = false;
} else {
    $emptyObserver = 'Select a name';
    $emptyType = 'Select a type';
}

echo $this->Form->create('Observer', array('class' => 'grainVersion'));
echo $this->Html->tag('fieldset', null);
echo $this->Html->tag('legend', __('Edit ' . $alias));
//echo $this->Form->input('id');
if (stristr($this->request->params['action'], 'Edit')) {
    echo $this->FgForm->secureId('Observer.id', $this->request->data['Observer']['id']);
}
if ($alias == 'Observer') {
    echo $this->Form->input('user_id', array('type' => 'hidden'));
	
	echo $this->Html->div('input select', NULL);
		echo $this->FgHtml->tag('label', 'User Observer', array('for' => 'ObserverUserObserverId'));
		// this is the email only list
		echo $this->FgHtml->tag('select', null, array(
			'id' => 'ObserverUserObserverId',
			'name' => 'data[Observer][user_observer_id]'
		));
			echo $this->FgHtml->tag('option', 'Select an observer', array('value' => ''));
			$this->FgHtml->setSelected(array($selectedUser));
			echo $this->FgHtml->recursiveTree('observer', $userObservers, $rootNodes);
		echo '</select>';
	echo '</div>';
	
} else {
	
	
	echo $this->Html->div('input select', NULL);
		echo $this->FgHtml->tag('label', 'User Observer', array('for' => 'ObserverUserObserverId'));
		// this is the anybody list
		echo $this->FgHtml->tag('select', null, array(
			'id' => 'ObserverUserId',
			'name' => 'data[Observer][user_id]'
		));
			echo $this->FgHtml->tag('option', 'Select an observed user', array('value' => ''));
			$this->FgHtml->setSelected(array($selectedUser));
			echo $this->FgHtml->recursiveTree('observed', $userObservers, $rootNodes);
		echo '</select>';
	echo '</div>';
	
	echo $this->Form->input('user_observer_id', array('type' => 'hidden'));
}
//		echo $this->Form->input('name', array('options' => $names, 'empty' => true));
echo $this->Form->input('type', array('options' => $types, 'empty' => $emptyType));
echo '</fieldset>';
echo $this->FgForm->button('Cancel', array(
    'type' => 'button',
    'bind' => 'click.basicCancelButton'
));
echo $this->FgForm->button('Submit', array(
    'type' => 'submit',
    'class' => 'submitButton'
));
echo $this->Form->end();
?>
