<div class="gateways form">
<?php echo $this->Form->create('Gateway'); ?>
	<fieldset>
		<legend><?php echo __('Edit Gateway'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('model_id');
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

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Gateway.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Gateway.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Gateways'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
