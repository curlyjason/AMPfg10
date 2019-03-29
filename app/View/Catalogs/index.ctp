<div class="catalogs index">
	<h2><?php echo __('Catalogs'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th><?php echo $this->Paginator->sort('item_id'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('parent_id'); ?></th>
			<th><?php echo $this->Paginator->sort('ancestor_list'); ?></th>
			<th><?php echo $this->Paginator->sort('item_count'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($catalogs as $catalog): ?>
	<tr>
		<td><?php echo h($catalog['Catalog']['id']); ?>&nbsp;</td>
		<td><?php echo h($catalog['Catalog']['created']); ?>&nbsp;</td>
		<td><?php echo h($catalog['Catalog']['modified']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($catalog['Item']['name'], array('controller' => 'items', 'action' => 'view', $catalog['Item']['id'])); ?>
		</td>
		<td><?php echo h($catalog['Catalog']['name']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($catalog['ParentCatalog']['name'], array('controller' => 'catalogs', 'action' => 'view', $catalog['ParentCatalog']['id'])); ?>
		</td>
		<td><?php echo h($catalog['Catalog']['ancestor_list']); ?>&nbsp;</td>
		<td><?php echo h($catalog['Catalog']['item_count']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $catalog['Catalog']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $catalog['Catalog']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $catalog['Catalog']['id']), null, __('Are you sure you want to delete # %s?', $catalog['Catalog']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Catalog'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Items'), array('controller' => 'items', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Item'), array('controller' => 'items', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Catalogs'), array('controller' => 'catalogs', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Parent Catalog'), array('controller' => 'catalogs', 'action' => 'add')); ?> </li>
	</ul>
</div>
