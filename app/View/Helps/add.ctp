<div class="helps form">
<?php echo $this->Form->create('Help'); ?>
	<fieldset>
		<legend><?php echo __('Add Help'); ?></legend>
	<?php
		echo $this->Form->input('name');
		echo $this->Form->input('help');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Helps'), array('action' => 'index')); ?></li>
	</ul>
</div>
