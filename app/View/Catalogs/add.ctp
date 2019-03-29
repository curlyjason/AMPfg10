<div class="catalogs form">
    <?php echo $this->Form->create('Catalog'); ?>
    <fieldset>
<?php

	// ============================= START FIELDS FOR AJAX TREE EDIT ADD FORM
        echo $this->Html->tag('div', null, array('class'=>'ajaxPull ajaxEditPull'));
?>
        <legend><?php echo __('Add Catalog'); ?></legend>
        <?php
        echo $this->Form->input('item_id', array('items' => $items, 'empty' => true));
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
	    'type' => 'radio',
	    'legend' => false,
	    'value' => 1,
	    'empty' => false
	));
        echo $this->Form->input('name');
        echo '</div>';
	// ============================= START FIELDS FOR AJAX TREE EDIT ADD FORM

	echo $this->Form->input('parent_id', array('options' => $parentCatalogs, 'empty' => true));
        ?>
    </fieldset>
    <?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
    <h3><?php echo __('Actions'); ?></h3>
    <ul>

        <li><?php echo $this->Html->link(__('List Catalogs'), array('action' => 'index')); ?></li>
        <li><?php echo $this->Html->link(__('List Items'), array('controller' => 'items', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Item'), array('controller' => 'items', 'action' => 'add')); ?> </li>
        <li><?php echo $this->Html->link(__('List Catalogs'), array('controller' => 'catalogs', 'action' => 'index')); ?> </li>
        <li><?php echo $this->Html->link(__('New Parent Catalog'), array('controller' => 'catalogs', 'action' => 'add')); ?> </li>
    </ul>
</div>
