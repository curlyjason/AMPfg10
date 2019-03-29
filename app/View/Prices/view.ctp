<div class="prices view">
<h2><?php echo __('Price'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($price['Price']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Customer'); ?></dt>
		<dd>
			<?php echo $this->Html->link($price['Customer']['id'], array('controller' => 'customers', 'action' => 'view', $price['Customer']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Min Qty'); ?></dt>
		<dd>
			<?php echo h($price['Price']['min_qty']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Max Qty'); ?></dt>
		<dd>
			<?php echo h($price['Price']['max_qty']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Price'); ?></dt>
		<dd>
			<?php echo h($price['Price']['price']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Test Max Qty'); ?></dt>
		<dd>
			<?php echo h($price['Price']['test_max_qty']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Price'), array('action' => 'edit', $price['Price']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Price'), array('action' => 'delete', $price['Price']['id']), null, __('Are you sure you want to delete # %s?', $price['Price']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Prices'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Price'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Customer'), array('controller' => 'customers', 'action' => 'add')); ?> </li>
	</ul>
</div>
