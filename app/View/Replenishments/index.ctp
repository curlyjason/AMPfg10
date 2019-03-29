<div class="replenishments index">
	<h2><?php echo __('Replenishments'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('first_name'); ?></th>
			<th><?php echo $this->Paginator->sort('last_name'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id'); ?></th>
			<th><?php echo $this->Paginator->sort('status'); ?></th>
			<th><?php echo $this->Paginator->sort('email'); ?></th>
			<th><?php echo $this->Paginator->sort('phone'); ?></th>
			<th><?php echo $this->Paginator->sort('billing_company'); ?></th>
			<th><?php echo $this->Paginator->sort('billing_address'); ?></th>
			<th><?php echo $this->Paginator->sort('billing_address2'); ?></th>
			<th><?php echo $this->Paginator->sort('billing_city'); ?></th>
			<th><?php echo $this->Paginator->sort('billing_zip'); ?></th>
			<th><?php echo $this->Paginator->sort('billing_state'); ?></th>
			<th><?php echo $this->Paginator->sort('billing_country'); ?></th>
			<th><?php echo $this->Paginator->sort('company'); ?></th>
			<th><?php echo $this->Paginator->sort('address'); ?></th>
			<th><?php echo $this->Paginator->sort('address2'); ?></th>
			<th><?php echo $this->Paginator->sort('city'); ?></th>
			<th><?php echo $this->Paginator->sort('zip'); ?></th>
			<th><?php echo $this->Paginator->sort('state'); ?></th>
			<th><?php echo $this->Paginator->sort('country'); ?></th>
			<th><?php echo $this->Paginator->sort('weight'); ?></th>
			<th><?php echo $this->Paginator->sort('order_item_count'); ?></th>
			<th><?php echo $this->Paginator->sort('subtotal'); ?></th>
			<th><?php echo $this->Paginator->sort('tax'); ?></th>
			<th><?php echo $this->Paginator->sort('shipping'); ?></th>
			<th><?php echo $this->Paginator->sort('total'); ?></th>
			<th><?php echo $this->Paginator->sort('order_type'); ?></th>
			<th><?php echo $this->Paginator->sort('authorization'); ?></th>
			<th><?php echo $this->Paginator->sort('transaction'); ?></th>
			<th><?php echo $this->Paginator->sort('ip_address'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th><?php echo $this->Paginator->sort('budget_id'); ?></th>
			<th><?php echo $this->Paginator->sort('user_customer_id'); ?></th>
			<th><?php echo $this->Paginator->sort('vendor_id'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($replenishments as $replenishment): ?>
	<tr>
		<td><?php echo h($replenishment['Replenishment']['id']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['first_name']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['last_name']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['user_id']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['status']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['email']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['phone']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['billing_company']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['billing_address']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['billing_address2']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['billing_city']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['billing_zip']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['billing_state']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['billing_country']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['company']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['address']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['address2']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['city']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['zip']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['state']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['country']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['weight']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['order_item_count']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['subtotal']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['tax']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['shipping']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['total']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['order_type']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['authorization']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['transaction']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['ip_address']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['created']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['modified']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['budget_id']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['user_customer_id']); ?>&nbsp;</td>
		<td><?php echo h($replenishment['Replenishment']['vendor_id']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $replenishment['Replenishment']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $replenishment['Replenishment']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $replenishment['Replenishment']['id']), null, __('Are you sure you want to delete # %s?', $replenishment['Replenishment']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Replenishment'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Replenishment Items'), array('controller' => 'replenishment_items', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Replenishment Item'), array('controller' => 'replenishment_items', 'action' => 'add')); ?> </li>
	</ul>
</div>
