<div class="passwordChange form">
    <?php
    echo $this->Session->flash('auth');
    echo $this->Form->create('User');
    echo '<fieldset>';
	echo $this->Html->para('', 'New Password for ' . $this->Session->read('Auth.User.username'));
	$type = ($requireCurrent) ? 'password' : 'hidden';
    // echo $this->FgForm->input('current', array('label' => 'Exposed Current', 'value' => $password));
	echo $this->Form->input('currentPassword', array('label' => 'Current Password', 'type' => $type, 'value' => $password, 'p' => $password));
    echo $this->Form->input('password', array('label' => 'New Password'));
    echo $this->Form->input('verifyPassword', array('label' => 'Repeat New Password', 'type' => 'password'));
	echo $this->Form->input('id', array('value' => $this->Session->read('Auth.User.id')));
    echo $this->Form->end(__('Reset Password'));
    ?>
</div>