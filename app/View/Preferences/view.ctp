<div class="preferences view">
<h2><?php echo __('Preference'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($preference['Preference']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($preference['Preference']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($preference['Preference']['modified']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Prefs'); ?></dt>
		<dd>
			<?php echo h($preference['Preference']['prefs']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User'); ?></dt>
		<dd>
			<?php echo $this->Html->link($preference['User']['id'], array('controller' => 'users', 'action' => 'view', $preference['User']['id'])); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Preference'), array('action' => 'edit', $preference['Preference']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Preference'), array('action' => 'delete', $preference['Preference']['id']), null, __('Are you sure you want to delete # %s?', $preference['Preference']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Preferences'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Preference'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
