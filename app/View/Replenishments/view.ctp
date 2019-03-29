<div class="replenishments view">
<h2><?php echo __('Replenishment'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('First Name'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['first_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Last Name'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['last_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User Id'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['user_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['status']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Email'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['email']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Phone'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['phone']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Billing Company'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['billing_company']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Billing Address'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['billing_address']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Billing Address2'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['billing_address2']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Billing City'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['billing_city']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Billing Zip'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['billing_zip']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Billing State'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['billing_state']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Billing Country'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['billing_country']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Shipping Company'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['company']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Shipping Address'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['address']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Shipping Address2'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['address2']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Shipping City'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['city']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Shipping Zip'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['zip']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Shipping State'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['state']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Shipping Country'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['country']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Weight'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['weight']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Order Item Count'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['order_item_count']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Subtotal'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['subtotal']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Tax'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['tax']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Shipping'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['shipping']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Total'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['total']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Order Type'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['order_type']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Authorization'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['authorization']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Transaction'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['transaction']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Ip Address'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['ip_address']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['modified']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Budget Id'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['budget_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User Customer Id'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['user_customer_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Vendor Id'); ?></dt>
		<dd>
			<?php echo h($replenishment['Replenishment']['vendor_id']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Replenishment'), array('action' => 'edit', $replenishment['Replenishment']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Replenishment'), array('action' => 'delete', $replenishment['Replenishment']['id']), null, __('Are you sure you want to delete # %s?', $replenishment['Replenishment']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Replenishments'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Replenishment'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Replenishment Items'), array('controller' => 'replenishment_items', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Replenishment Item'), array('controller' => 'replenishment_items', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Replenishment Items'); ?></h3>
	<?php if (!empty($replenishment['ReplenishmentItem'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Replenishment Id'); ?></th>
		<th><?php echo __('Item Id'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('Quantity'); ?></th>
		<th><?php echo __('Weight'); ?></th>
		<th><?php echo __('Price'); ?></th>
		<th><?php echo __('Subtotal'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($replenishment['ReplenishmentItem'] as $replenishmentItem): ?>
		<tr>
			<td><?php echo $replenishmentItem['id']; ?></td>
			<td><?php echo $replenishmentItem['replenishment_id']; ?></td>
			<td><?php echo $replenishmentItem['item_id']; ?></td>
			<td><?php echo $replenishmentItem['name']; ?></td>
			<td><?php echo $replenishmentItem['quantity']; ?></td>
			<td><?php echo $replenishmentItem['weight']; ?></td>
			<td><?php echo $replenishmentItem['price']; ?></td>
			<td><?php echo $replenishmentItem['subtotal']; ?></td>
			<td><?php echo $replenishmentItem['created']; ?></td>
			<td><?php echo $replenishmentItem['modified']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'replenishment_items', 'action' => 'view', $replenishmentItem['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'replenishment_items', 'action' => 'edit', $replenishmentItem['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'replenishment_items', 'action' => 'delete', $replenishmentItem['id']), null, __('Are you sure you want to delete # %s?', $replenishmentItem['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Replenishment Item'), array('controller' => 'replenishment_items', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
