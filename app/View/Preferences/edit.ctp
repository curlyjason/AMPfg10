<div class="preferences form">
<?php echo $this->Form->create('Preference'); ?>
	<fieldset>
		<legend><?php echo __('Edit Preference'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('prefs');
		echo $this->Form->input('user_id');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Preference.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Preference.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Preferences'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
