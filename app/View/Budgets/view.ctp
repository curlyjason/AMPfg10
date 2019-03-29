<div class="budgets view">
<h2><?php echo __('Budget'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($budget['Budget']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('User'); ?></dt>
		<dd>
			<?php echo $this->Html->link($budget['User']['username'], array('controller' => 'users', 'action' => 'view', $budget['User']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Use Budget'); ?></dt>
		<dd>
			<?php echo h($budget['Budget']['use_budget']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Budget'); ?></dt>
		<dd>
			<?php echo h($budget['Budget']['budget']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Remaining Budget'); ?></dt>
		<dd>
			<?php echo h($budget['Budget']['remaining_budget']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Use Item Budget'); ?></dt>
		<dd>
			<?php echo h($budget['Budget']['use_item_budget']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Item Budget'); ?></dt>
		<dd>
			<?php echo h($budget['Budget']['item_budget']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Remaining Item Budget'); ?></dt>
		<dd>
			<?php echo h($budget['Budget']['remaining_item_budget']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Budget Month'); ?></dt>
		<dd>
			<?php echo h($budget['Budget']['budget_month']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Current'); ?></dt>
		<dd>
			<?php echo h($budget['Budget']['current']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($budget['Budget']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($budget['Budget']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Budget'), array('action' => 'edit', $budget['Budget']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Budget'), array('action' => 'delete', $budget['Budget']['id']), null, __('Are you sure you want to delete # %s?', $budget['Budget']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Budgets'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Budget'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
