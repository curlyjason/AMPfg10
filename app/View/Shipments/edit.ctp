<div class="shipments form">
<?php echo $this->Form->create('Shipment'); ?>
	<fieldset>
		<legend><?php echo __('Edit Shipment'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('order_id');
		echo $this->Form->input('carrier');
		echo $this->Form->input('method');
		echo $this->Form->input('tracking');
		echo $this->Form->input('status');
		echo $this->Form->input('carrier_notes');
		echo $this->Form->input('shipment_cost');
		echo $this->Form->input('first_name');
		echo $this->Form->input('last_name');
		echo $this->Form->input('email');
		echo $this->Form->input('phone');
		echo $this->Form->input('address');
		echo $this->Form->input('address2');
		echo $this->Form->input('city');
		echo $this->Form->input('zip');
		echo $this->Form->input('state');
		echo $this->Form->input('country');
		echo $this->Form->input('weight');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Shipment.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Shipment.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Shipments'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?> </li>
	</ul>
</div>
