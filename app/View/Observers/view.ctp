<div class="observers view">
<h2><?php echo __('Observer'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($observer['Observer']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($observer['Observer']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($observer['Observer']['modified']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User'); ?></dt>
		<dd>
			<?php echo $this->Html->link($observer['User']['id'], array('controller' => 'users', 'action' => 'view', $observer['User']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User Observer Id'); ?></dt>
		<dd>
			<?php echo h($observer['Observer']['user_observer_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($observer['Observer']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Type'); ?></dt>
		<dd>
			<?php echo h($observer['Observer']['type']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Observer'), array('action' => 'edit', $observer['Observer']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Observer'), array('action' => 'delete', $observer['Observer']['id']), null, __('Are you sure you want to delete # %s?', $observer['Observer']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Observers'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Observer'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
