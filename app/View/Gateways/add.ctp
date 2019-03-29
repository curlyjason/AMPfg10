<div class="gateways form">
<?php echo $this->Form->create('Gateway'); ?>
	<fieldset>
		<legend><?php echo __('Add Gateway'); ?></legend>
	<?php
		echo $this->Form->input('model_id', array(
			'type' => 'text',
			'label' => 'Target ID'
		));
		echo $this->Form->input('model_alias');
		echo $this->Form->input('user_id');
		echo $this->Form->input('complete');
		echo $this->Form->input('action');
		echo $this->Form->input('controller');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Gateways'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
