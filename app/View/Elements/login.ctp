<div class="users form">
    <?php echo $this->Flash->render('auth');
    echo $this->fetch('timeout');
    echo $this->Form->create('User', [
            'url' => ['controller' => 'users', 'action' => 'login']

    ]);
    echo '<fieldset>';
		echo $this->Html->tag('legend', 'Please enter your username and password');
		echo $this->Form->input('username', array('tabindex' => 1));
		echo $this->Form->input('Forgot Password', array(
			'type' => 'checkbox',
			'id' => 'forgot',
			'bind' => 'change.forgotPassword',
			'tabindex' => 0));
		echo $this->Form->input('password', array('tabindex' => 2));
    echo '</fieldset>';
echo $this->Form->end(__('Login')); ?>
</div>