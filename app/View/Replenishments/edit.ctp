<div class="replenishments form">
<?php echo $this->Form->create('Replenishment'); ?>
	<fieldset>
		<legend><?php echo __('Edit Replenishment'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('first_name');
		echo $this->Form->input('last_name');
		echo $this->Form->input('user_id');
		echo $this->Form->input('status');
		echo $this->Form->input('email');
		echo $this->Form->input('phone');
		echo $this->Form->input('billing_company');
		echo $this->Form->input('billing_address');
		echo $this->Form->input('billing_address2');
		echo $this->Form->input('billing_city');
		echo $this->Form->input('billing_zip');
		echo $this->Form->input('billing_state');
		echo $this->Form->input('billing_country');
		echo $this->Form->input('company');
		echo $this->Form->input('address');
		echo $this->Form->input('address2');
		echo $this->Form->input('city');
		echo $this->Form->input('zip');
		echo $this->Form->input('state');
		echo $this->Form->input('country');
		echo $this->Form->input('weight');
		echo $this->Form->input('order_item_count');
		echo $this->Form->input('subtotal');
		echo $this->Form->input('tax');
		echo $this->Form->input('shipping');
		echo $this->Form->input('total');
		echo $this->Form->input('order_type');
		echo $this->Form->input('authorization');
		echo $this->Form->input('transaction');
		echo $this->Form->input('ip_address');
		echo $this->Form->input('budget_id');
		echo $this->Form->input('user_customer_id');
		echo $this->Form->input('vendor_id');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Replenishment.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Replenishment.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Replenishments'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Replenishment Items'), array('controller' => 'replenishment_items', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Replenishment Item'), array('controller' => 'replenishment_items', 'action' => 'add')); ?> </li>
	</ul>
</div>
