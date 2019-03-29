<div class="images index">
	<h2><?php echo __('Images'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('image'); ?></th>
			<th><?php echo $this->Paginator->sort('img_file'); ?></th>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('mimetype'); ?></th>
			<th><?php echo $this->Paginator->sort('filesize'); ?></th>
			<th><?php echo $this->Paginator->sort('width'); ?></th>
			<th><?php echo $this->Paginator->sort('height'); ?></th>
			<th><?php echo $this->Paginator->sort('title'); ?></th>
			<th><?php echo $this->Paginator->sort('date'); ?></th>
			<th><?php echo $this->Paginator->sort('category'); ?></th>
			<th><?php echo $this->Paginator->sort('alt'); ?></th>
			<th><?php echo $this->Paginator->sort('upload'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($images as $image): ?>
	<tr>
		<td><?php echo $this->Html->image('images' .DS. 'thumb' .DS. 'x75y56' .DS. $image['Image']['img_file']); ?>&nbsp;</td>
		<td><?php echo h($image['Image']['img_file']); ?>&nbsp;</td>
		<td><?php echo h($image['Image']['id']); ?>&nbsp;</td>
		<td><?php echo h($image['Image']['mimetype']); ?>&nbsp;</td>
		<td><?php echo h($image['Image']['filesize']); ?>&nbsp;</td>
		<td><?php echo h($image['Image']['width']); ?>&nbsp;</td>
		<td><?php echo h($image['Image']['height']); ?>&nbsp;</td>
		<td><?php echo h($image['Image']['title']); ?>&nbsp;</td>
		<td><?php echo h($image['Image']['date']); ?>&nbsp;</td>
		<td><?php echo h($image['Image']['category']); ?>&nbsp;</td>
		<td><?php echo h($image['Image']['alt']); ?>&nbsp;</td>
		<td><?php echo h($image['Image']['upload']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $image['Image']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $image['Image']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $image['Image']['id']), null, __('Are you sure you want to delete # %s?', $image['Image']['id'])); ?>
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
		<li><?php echo $this->Html->link(__('New Image'), array('action' => 'add')); ?></li>
	</ul>
</div>
