<div class="orders view">
<h2><?php echo __('Order'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($order['Order']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($order['Order']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($order['Order']['modified']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($order['Order']['name']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Order'), array('action' => 'edit', $order['Order']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Order'), array('action' => 'delete', $order['Order']['id']), null, __('Are you sure you want to delete # %s?', $order['Order']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Orders'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Order'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Order Items'), array('controller' => 'order_items', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Order Item'), array('controller' => 'order_items', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Shipments'), array('controller' => 'shipments', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Shipment'), array('controller' => 'shipments', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Order Items'); ?></h3>
	<?php if (!empty($order['OrderItem'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Order Id'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($order['OrderItem'] as $orderItem): ?>
		<tr>
			<td><?php echo $orderItem['id']; ?></td>
			<td><?php echo $orderItem['order_id']; ?></td>
			<td><?php echo $orderItem['created']; ?></td>
			<td><?php echo $orderItem['modified']; ?></td>
			<td><?php echo $orderItem['name']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'order_items', 'action' => 'view', $orderItem['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'order_items', 'action' => 'edit', $orderItem['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'order_items', 'action' => 'delete', $orderItem['id']), null, __('Are you sure you want to delete # %s?', $orderItem['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Order Item'), array('controller' => 'order_items', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
<div class="related">
	<h3><?php echo __('Related Shipments'); ?></h3>
	<?php if (!empty($order['Shipment'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Order Id'); ?></th>
		<th><?php echo __('Carrier'); ?></th>
		<th><?php echo __('Method'); ?></th>
		<th><?php echo __('Tracking'); ?></th>
		<th><?php echo __('Status'); ?></th>
		<th><?php echo __('Carrier Notes'); ?></th>
		<th><?php echo __('Shipment Cost'); ?></th>
		<th><?php echo __('First Name'); ?></th>
		<th><?php echo __('Last Name'); ?></th>
		<th><?php echo __('Email'); ?></th>
		<th><?php echo __('Phone'); ?></th>
		<th><?php echo __('Shipping Address'); ?></th>
		<th><?php echo __('Shipping Address2'); ?></th>
		<th><?php echo __('Shipping City'); ?></th>
		<th><?php echo __('Shipping Zip'); ?></th>
		<th><?php echo __('Shipping State'); ?></th>
		<th><?php echo __('Shipping Country'); ?></th>
		<th><?php echo __('Weight'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($order['Shipment'] as $shipment): ?>
		<tr>
			<td><?php echo $shipment['id']; ?></td>
			<td><?php echo $shipment['order_id']; ?></td>
			<td><?php echo $shipment['carrier']; ?></td>
			<td><?php echo $shipment['method']; ?></td>
			<td><?php echo $shipment['tracking']; ?></td>
			<td><?php echo $shipment['status']; ?></td>
			<td><?php echo $shipment['carrier_notes']; ?></td>
			<td><?php echo $shipment['shipment_cost']; ?></td>
			<td><?php echo $shipment['first_name']; ?></td>
			<td><?php echo $shipment['last_name']; ?></td>
			<td><?php echo $shipment['email']; ?></td>
			<td><?php echo $shipment['phone']; ?></td>
			<td><?php echo $shipment['address']; ?></td>
			<td><?php echo $shipment['address2']; ?></td>
			<td><?php echo $shipment['city']; ?></td>
			<td><?php echo $shipment['zip']; ?></td>
			<td><?php echo $shipment['state']; ?></td>
			<td><?php echo $shipment['country']; ?></td>
			<td><?php echo $shipment['weight']; ?></td>
			<td><?php echo $shipment['created']; ?></td>
			<td><?php echo $shipment['modified']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'shipments', 'action' => 'view', $shipment['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'shipments', 'action' => 'edit', $shipment['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'shipments', 'action' => 'delete', $shipment['id']), null, __('Are you sure you want to delete # %s?', $shipment['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Shipment'), array('controller' => 'shipments', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
