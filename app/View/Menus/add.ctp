<div class="menus form">
<?php echo $this->Form->create('Menu'); ?>
	<fieldset>
		<legend><?php echo __('Add Menu'); ?></legend>
	<?php
		echo $this->Form->select('parent_id',$parentMenus);
//		echo $this->Form->input('lft', $options);
//		echo $this->Form->input('rght');
		echo $this->Form->input('name');
		echo $this->Form->select('group', array(
            'Client' => 'Client',
            'Staff' => 'Staff',
            'Warehouses' => 'Warehouse',
            'Admins' => 'Admin'
        ), array('value' => 'Client', 'empty' => false));
		echo $this->Form->select('access', array(
            'Guest' => 'Guest',
            'Buyer' => 'Buyer',
            'Manager' => 'Manager'
        ), array('value' => 'Guest', 'empty' => false));
		echo $this->Form->input('controller');
		echo $this->Form->input('action');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Menus'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Menus'), array('controller' => 'menus', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Parent Menu'), array('controller' => 'menus', 'action' => 'add')); ?> </li>
	</ul>
</div>
