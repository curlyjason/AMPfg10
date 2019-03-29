<div class="budgets form">
<?php echo $this->Form->create('Budget'); ?>
	<fieldset>
		<legend><?php echo __('Add Budget'); ?></legend>
	<?php
		echo $this->Form->input('user_id');
		echo $this->Form->input('use_budget');
		echo $this->Form->input('budget');
		echo $this->Form->input('remaining_budget');
		echo $this->Form->input('use_item_budget');
		echo $this->Form->input('item_budget');
		echo $this->Form->input('remaining_item_budget');
		echo $this->Form->input('budget_month');
		echo $this->Form->input('current');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Budgets'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
