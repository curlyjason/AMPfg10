<div class="gateways index">
	<h2><?php echo __('Gateways'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('model_id'); ?></th>
			<th><?php echo $this->Paginator->sort('model_alias'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id'); ?></th>
			<th><?php echo $this->Paginator->sort('complete'); ?></th>
			<th><?php echo $this->Paginator->sort('action'); ?></th>
			<th><?php echo $this->Paginator->sort('controller'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($gateways as $gateway): ?>
	<tr>
		<td><?php echo h($gateway['Gateway']['id']); ?>&nbsp;</td>
		<td><?php echo h($gateway['Gateway']['model_id']); ?>&nbsp;</td>
		<td><?php echo h($gateway['Gateway']['model_alias']); ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($gateway['User']['username'], array('controller' => 'users', 'action' => 'view', $gateway['User']['id'])); ?>
		</td>
		<td><?php echo h($gateway['Gateway']['complete']); ?>&nbsp;</td>
		<td><?php echo h($gateway['Gateway']['action']); ?>&nbsp;</td>
		<td><?php echo h($gateway['Gateway']['controller']); ?>&nbsp;</td>
		<td><?php echo h($gateway['Gateway']['created']); ?>&nbsp;</td>
		<td><?php echo h($gateway['Gateway']['modified']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $gateway['Gateway']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $gateway['Gateway']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $gateway['Gateway']['id']), null, __('Are you sure you want to delete # %s?', $gateway['Gateway']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Gateway'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
