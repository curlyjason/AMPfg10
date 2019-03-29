<div class="helps form">
<?php echo $this->Form->create('Help'); ?>
	<fieldset>
		<legend><?php echo __('Edit Help'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('help');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Help.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Help.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Helps'), array('action' => 'index')); ?></li>
	</ul>
</div>
