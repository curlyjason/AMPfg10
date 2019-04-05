<?php echo $this->Html->script('documents')?>
<div class="documents form">
<?php echo $this->Form->create('CrudDoc', array('type' => 'file')); ?>
	<fieldset>
		<legend><?php echo __('Add Document'); ?></legend>
	<?php
		echo $this->Form->input('order_id');
		echo $this->Form->input('img_file', array('type' => 'file', 'bind' => 'change.captureName'));
		echo $this->Form->input('dir', array('type' => 'hidden'));
		echo $this->Form->input('title');
		echo $this->Form->input('instructions');
		echo $this->Form->input('printed');
		echo $this->Form->input('count');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Documents'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Customer'), array('controller' => 'customers', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?> </li>
	</ul>
</div>
