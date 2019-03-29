<div class="replenishmentItems form">
<?php echo $this->Form->create('ReplenishmentItem'); ?>
	<fieldset>
		<legend><?php echo __('Add Replenishment Item'); ?></legend>
	<?php
		echo $this->Form->input('replenishment_id');
		echo $this->Form->input('item_id');
		echo $this->Form->input('name');
		echo $this->Form->input('quantity');
		echo $this->Form->input('weight');
		echo $this->Form->input('price');
		echo $this->Form->input('subtotal');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Replenishment Items'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Replenishments'), array('controller' => 'replenishments', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Replenishment'), array('controller' => 'replenishments', 'action' => 'add')); ?> </li>
	</ul>
</div>
