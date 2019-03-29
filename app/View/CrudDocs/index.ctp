<div class="documents index">
	<h2><?php echo __('Documents'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th><?php echo $this->Paginator->sort('img_file'); ?></th>
			<th><?php echo $this->Paginator->sort('dir'); ?></th>
			<th><?php echo $this->Paginator->sort('title'); ?></th>
			<th><?php echo $this->Paginator->sort('instructions'); ?></th>
			<th><?php echo $this->Paginator->sort('order_id'); ?></th>
			<th><?php echo $this->Paginator->sort('printed'); ?></th>
			<th><?php echo $this->Paginator->sort('count'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($documents as $document): ?>
	<tr>
		<td><?php echo h($document['CrudDoc']['id']); ?>&nbsp;</td>
		<td><?php echo h($document['CrudDoc']['created']); ?>&nbsp;</td>
		<td><?php echo h($document['CrudDoc']['modified']); ?>&nbsp;</td>
		<td><?php echo h($document['CrudDoc']['img_file']); ?>&nbsp;</td>
		<td><?php echo h($document['CrudDoc']['dir']); ?>&nbsp;</td>
		<td><?php echo h($document['CrudDoc']['title']); ?>&nbsp;</td>
		<td><?php echo h($document['CrudDoc']['instructions']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($document['Order']['id'], array('controller' => 'orders', 'action' => 'view', $document['Order']['id'])); ?>
		</td>
		<td><?php echo h($document['CrudDoc']['printed']); ?>&nbsp;</td>
		<td><?php echo h($document['CrudDoc']['count']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $document['CrudDoc']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $document['CrudDoc']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $document['CrudDoc']['id']), null, __('Are you sure you want to delete # %s?', $document['CrudDoc']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Document'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Customers'), array('controller' => 'customers', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Customer'), array('controller' => 'customers', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?> </li>
	</ul>
</div>
