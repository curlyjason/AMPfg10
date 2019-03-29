<div class="replenishmentItems index">
	<h2><?php echo __('Replenishment Items'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('replenishment_id'); ?></th>
			<th><?php echo $this->Paginator->sort('item_id'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('quantity'); ?></th>
			<th><?php echo $this->Paginator->sort('weight'); ?></th>
			<th><?php echo $this->Paginator->sort('price'); ?></th>
			<th><?php echo $this->Paginator->sort('subtotal'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($replenishmentItems as $replenishmentItem): ?>
	<tr>
		<td><?php echo h($replenishmentItem['ReplenishmentItem']['id']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($replenishmentItem['Replenishment']['id'], array('controller' => 'replenishments', 'action' => 'view', $replenishmentItem['Replenishment']['id'])); ?>
		</td>
		<td><?php echo h($replenishmentItem['ReplenishmentItem']['item_id']); ?>&nbsp;</td>
		<td><?php echo h($replenishmentItem['ReplenishmentItem']['name']); ?>&nbsp;</td>
		<td><?php echo h($replenishmentItem['ReplenishmentItem']['quantity']); ?>&nbsp;</td>
		<td><?php echo h($replenishmentItem['ReplenishmentItem']['weight']); ?>&nbsp;</td>
		<td><?php echo h($replenishmentItem['ReplenishmentItem']['price']); ?>&nbsp;</td>
		<td><?php echo h($replenishmentItem['ReplenishmentItem']['subtotal']); ?>&nbsp;</td>
		<td><?php echo h($replenishmentItem['ReplenishmentItem']['created']); ?>&nbsp;</td>
		<td><?php echo h($replenishmentItem['ReplenishmentItem']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $replenishmentItem['ReplenishmentItem']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $replenishmentItem['ReplenishmentItem']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $replenishmentItem['ReplenishmentItem']['id']), null, __('Are you sure you want to delete # %s?', $replenishmentItem['ReplenishmentItem']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Replenishment Item'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Replenishments'), array('controller' => 'replenishments', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Replenishment'), array('controller' => 'replenishments', 'action' => 'add')); ?> </li>
	</ul>
</div>
