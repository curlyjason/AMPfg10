<div class="helps view">
<h2><?php echo __('Help'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($help['Help']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($help['Help']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($help['Help']['modified']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($help['Help']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Help'); ?></dt>
		<dd>
			<?php echo $this->FgHtml->markdown($help['Help']['help']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Help'), array('action' => 'edit', $help['Help']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Help'), array('action' => 'delete', $help['Help']['id']), null, __('Are you sure you want to delete # %s?', $help['Help']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Helps'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Help'), array('action' => 'add')); ?> </li>
	</ul>
</div>
