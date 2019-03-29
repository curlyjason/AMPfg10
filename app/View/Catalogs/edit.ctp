<?php // debug($this->request->data);?>
<div class="catalogs form">
<?php echo $this->Form->create('Catalog'); ?>
	<fieldset>
		<legend><?php echo __('Edit Catalog'); ?></legend>
	<?php
		echo $this->Form->input('id');
        echo $this->Form->input('folder', array(
            'options' => array(
                        1 => 'Folder'
                    ),
            'value' => 0,
            'empty' => false,
            'type' => 'checkbox'
        ));
        echo $this->Form->input('active', array(
            'options' => array(
                        0 => 'Inactive',
                        1 => 'Active'
                    ),
            'value' => 1,
            'empty' => false
        ));
		echo $this->Form->input('item_id', array('items' => $items));
		echo $this->Form->input('name');
		echo $this->Form->input('parent_id', array('options'=>$parentCatalogs));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Catalog.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Catalog.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Catalogs'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Items'), array('controller' => 'items', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Item'), array('controller' => 'items', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Catalogs'), array('controller' => 'catalogs', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Parent Catalog'), array('controller' => 'catalogs', 'action' => 'add')); ?> </li>
	</ul>
</div>
