<div class="gateways view">
<h2><?php echo __('Gateway'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($gateway['Gateway']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Model Id'); ?></dt>
		<dd>
			<?php echo h($gateway['Gateway']['model_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Model Alias'); ?></dt>
		<dd>
			<?php echo h($gateway['Gateway']['model_alias']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User'); ?></dt>
		<dd>
			<?php echo $this->Html->link($gateway['User']['username'], array('controller' => 'users', 'action' => 'view', $gateway['User']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Complete'); ?></dt>
		<dd>
			<?php echo h($gateway['Gateway']['complete']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Action'); ?></dt>
		<dd>
			<?php echo h($gateway['Gateway']['action']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Controller'); ?></dt>
		<dd>
			<?php echo h($gateway['Gateway']['controller']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($gateway['Gateway']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($gateway['Gateway']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Gateway'), array('action' => 'edit', $gateway['Gateway']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Gateway'), array('action' => 'delete', $gateway['Gateway']['id']), null, __('Are you sure you want to delete # %s?', $gateway['Gateway']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Gateways'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Gateway'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
