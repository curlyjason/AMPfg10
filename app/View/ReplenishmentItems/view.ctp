<div class="replenishmentItems view">
<h2><?php echo __('Replenishment Item'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($replenishmentItem['ReplenishmentItem']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Replenishment'); ?></dt>
		<dd>
			<?php echo $this->Html->link($replenishmentItem['Replenishment']['id'], array('controller' => 'replenishments', 'action' => 'view', $replenishmentItem['Replenishment']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Item Id'); ?></dt>
		<dd>
			<?php echo h($replenishmentItem['ReplenishmentItem']['item_id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($replenishmentItem['ReplenishmentItem']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Quantity'); ?></dt>
		<dd>
			<?php echo h($replenishmentItem['ReplenishmentItem']['quantity']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Weight'); ?></dt>
		<dd>
			<?php echo h($replenishmentItem['ReplenishmentItem']['weight']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Price'); ?></dt>
		<dd>
			<?php echo h($replenishmentItem['ReplenishmentItem']['price']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Subtotal'); ?></dt>
		<dd>
			<?php echo h($replenishmentItem['ReplenishmentItem']['subtotal']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($replenishmentItem['ReplenishmentItem']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($replenishmentItem['ReplenishmentItem']['modified']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Replenishment Item'), array('action' => 'edit', $replenishmentItem['ReplenishmentItem']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Replenishment Item'), array('action' => 'delete', $replenishmentItem['ReplenishmentItem']['id']), null, __('Are you sure you want to delete # %s?', $replenishmentItem['ReplenishmentItem']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Replenishment Items'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Replenishment Item'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Replenishments'), array('controller' => 'replenishments', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Replenishment'), array('controller' => 'replenishments', 'action' => 'add')); ?> </li>
	</ul>
</div>
