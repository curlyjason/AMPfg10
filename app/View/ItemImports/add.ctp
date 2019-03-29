<div class="itemImports form">
<?php echo $this->Form->create('ItemImport'); ?>
	<fieldset>
		<legend><?php echo __('Add Item Import'); ?></legend>
	<?php
		echo $this->Form->input('field_name');
		echo $this->Form->input('import_name');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Item Imports'), array('action' => 'index')); ?></li>
	</ul>
</div>
