<div class="userForm">
    <?php
    echo $this->Session->flash();
    echo $this->FgForm->create('User', array('class' => 'grainVersion'));
    echo $this->FgForm->secureId('User.id', $id);
	if($this->request->data['User']['folder']){
		echo $this->Form->input('User.username', array('label' => 'name'));		
	} else {
		echo $this->Form->input('User.first_name');
		echo $this->Form->input('User.last_name');
		echo $this->Form->input('User.username', array('label' => 'email'));
		if ($this->Session->read('Auth.User.access') === 'Manager') {
			echo $this->Form->input('User.role', array('empty' => true));
			
			//=========================== Budgets
			echo $this->Html->tag('fieldset'); // Opening fieldset
			$marker = 'Budgets-' . $this->request->data['User']['id'];
			echo $this->Html->tag('legend', __('Budgets'), array('id' => $marker, 'class' => 'toggle'));
			echo $this->FgHtml->div($marker.' hide', NULL);
				echo $this->FgForm->folderCheck('User', 'use_budget', array('class' => 'use_budget'));
				if (isset($this->request->data['User']['use_budget'])) {
					echo $this->FgForm->folderCheck('User', 'rollover_budget', array('class' => 'rollover_budget'));
					echo $this->Form->input('User.budget');
				}
				echo $this->FgForm->folderCheck('User', 'use_item_budget', array('class' => 'use_item_budget'));
				if (isset($this->request->data['User']['use_item_budget'])) {
					echo $this->FgForm->folderCheck('User', 'rollover_item_budget', array('class' => 'rollover_item_budget'));
					echo $this->Form->input('User.item_budget');
				}
				echo $this->FgForm->folderCheck('User', 'use_item_limit_budget');

			echo '</div>'; // close toggling div
			echo '</fieldset>'; //close fieldset

			echo $this->FgForm->activeRadio();
			if (isset($this->request->data['Customer']['id'])) {
				echo $this->FgForm->secureId('Customer.id', $id);
				echo $this->Form->input('Customer.customer_type');
				echo $this->Form->input('Customer.order_contact');
				echo $this->Form->input('Customer.billing_contact');
				echo $this->FgForm->folderCheck('Customer', 'allow_backorder');
				echo $this->FgForm->folderCheck('Customer', 'allow_direct_pay');
			}
		}
	}
echo $this->FgForm->button('Cancel', array(
	'type' => 'button',
	'bind' => 'click.basicCancelButton'
    ));
    echo $this->FgForm->button('Submit', array(
	'type' => 'submit',
	'class' => 'submitButton'
    ));
    if ($this->Session->read('Auth.User.access') === 'Manager') {
        echo $this->Form->button('Reset Password', array(
            'bind' => 'click.reset_password', 
            'type' => 'button'
            ));
    }
    echo $this->Form->end();
    ?>
</div>