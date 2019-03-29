<div class="itemImports view">
<h2><?php echo __('Item Import'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($itemImport['ItemImport']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Field Name'); ?></dt>
		<dd>
			<?php echo h($itemImport['ItemImport']['field_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Import Name'); ?></dt>
		<dd>
			<?php echo h($itemImport['ItemImport']['import_name']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Item Import'), array('action' => 'edit', $itemImport['ItemImport']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Item Import'), array('action' => 'delete', $itemImport['ItemImport']['id']), null, __('Are you sure you want to delete # %s?', $itemImport['ItemImport']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Item Imports'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Item Import'), array('action' => 'add')); ?> </li>
	</ul>
</div>
