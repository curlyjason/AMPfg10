<div class="users view">
<h2><?php echo __('User'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($user['User']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Email'); ?></dt>
		<dd>
			<?php echo h($user['User']['email']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Password'); ?></dt>
		<dd>
			<?php echo h($user['User']['password']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('First Name'); ?></dt>
		<dd>
			<?php echo h($user['User']['first_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Last Name'); ?></dt>
		<dd>
			<?php echo h($user['User']['last_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Active'); ?></dt>
		<dd>
			<?php echo h($user['User']['active']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('username'); ?></dt>
		<dd>
			<?php echo h($user['User']['username']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Role'); ?></dt>
		<dd>
			<?php echo h($user['User']['role']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($user['User']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($user['User']['modified']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Parent User'); ?></dt>
		<dd>
			<?php echo $this->Html->link($user['ParentUser']['id'], array('controller' => 'users', 'action' => 'view', $user['ParentUser']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Ancestor List'); ?></dt>
		<dd>
			<?php echo h($user['User']['ancestor_list']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Item Count'); ?></dt>
		<dd>
			<?php echo h($user['User']['item_count']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Lock'); ?></dt>
		<dd>
			<?php echo h($user['User']['lock']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Sequence'); ?></dt>
		<dd>
			<?php echo h($user['User']['sequence']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit User'), array('action' => 'edit', $user['User']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete User'), array('action' => 'delete', $user['User']['id']), null, __('Are you sure you want to delete # %s?', $user['User']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Parent User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Times'), array('controller' => 'times', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Time'), array('controller' => 'times', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Times'); ?></h3>
	<?php if (!empty($user['Time'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th><?php echo __('User Id'); ?></th>
		<th><?php echo __('Project Id'); ?></th>
		<th><?php echo __('Time In'); ?></th>
		<th><?php echo __('Time Out'); ?></th>
		<th><?php echo __('Activity'); ?></th>
		<th><?php echo __('User'); ?></th>
		<th><?php echo __('Project'); ?></th>
		<th><?php echo __('Duration'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($user['Time'] as $time): ?>
		<tr>
			<td><?php echo $time['id']; ?></td>
			<td><?php echo $time['created']; ?></td>
			<td><?php echo $time['modified']; ?></td>
			<td><?php echo $time['user_id']; ?></td>
			<td><?php echo $time['project_id']; ?></td>
			<td><?php echo $time['time_in']; ?></td>
			<td><?php echo $time['time_out']; ?></td>
			<td><?php echo $time['activity']; ?></td>
			<td><?php echo $time['user']; ?></td>
			<td><?php echo $time['project']; ?></td>
			<td><?php echo $time['duration']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'times', 'action' => 'view', $time['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'times', 'action' => 'edit', $time['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'times', 'action' => 'delete', $time['id']), null, __('Are you sure you want to delete # %s?', $time['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Time'), array('controller' => 'times', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
<div class="related">
	<h3><?php echo __('Related Users'); ?></h3>
	<?php if (!empty($user['ChildUser'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Email'); ?></th>
		<th><?php echo __('Password'); ?></th>
		<th><?php echo __('First Name'); ?></th>
		<th><?php echo __('Last Name'); ?></th>
		<th><?php echo __('Active'); ?></th>
		<th><?php echo __('username'); ?></th>
		<th><?php echo __('Role'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th><?php echo __('Parent Id'); ?></th>
		<th><?php echo __('Ancestor List'); ?></th>
		<th><?php echo __('Item Count'); ?></th>
		<th><?php echo __('Lock'); ?></th>
		<th><?php echo __('Sequence'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($user['ChildUser'] as $childUser): ?>
		<tr>
			<td><?php echo $childUser['id']; ?></td>
			<td><?php echo $childUser['email']; ?></td>
			<td><?php echo $childUser['password']; ?></td>
			<td><?php echo $childUser['first_name']; ?></td>
			<td><?php echo $childUser['last_name']; ?></td>
			<td><?php echo $childUser['active']; ?></td>
			<td><?php echo $childUser['username']; ?></td>
			<td><?php echo $childUser['role']; ?></td>
			<td><?php echo $childUser['created']; ?></td>
			<td><?php echo $childUser['modified']; ?></td>
			<td><?php echo $childUser['parent_id']; ?></td>
			<td><?php echo $childUser['ancestor_list']; ?></td>
			<td><?php echo $childUser['item_count']; ?></td>
			<td><?php echo $childUser['lock']; ?></td>
			<td><?php echo $childUser['sequence']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'users', 'action' => 'view', $childUser['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'users', 'action' => 'edit', $childUser['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'users', 'action' => 'delete', $childUser['id']), null, __('Are you sure you want to delete # %s?', $childUser['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Child User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
