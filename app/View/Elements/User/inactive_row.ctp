<?php 
	if ($user['User']['active']) {
		$state = 'Active';
		$binding = 'Inactivate';
		$val = 0;
	} else {
		$state = 'Inactive';
		$binding = 'Activate';
		$val = 1;
	}
	$customer = !is_null($user['Customer']['id']);
	$secureUser = $this->FgHtml->secureSelect($user['User']['id'], 'li');
	$folder = $user['User']['folder'];
?>
	<tr id="<?php echo "row-{$user['User']['id']}" ?>">
		<td><?php  echo "[{$user['User']['id']}] " . h($customer ? $user['Customer']['name'] : $user['User']['ancestor_list']); ?>&nbsp;</td>
		<td><?php echo $customer ? "CUSTOMER: {$user['Customer']['name']}" : ($folder ? "FOLDER: {$user['Customer']['name']}" : h($user['User']['name'])); ?>&nbsp;</td>
		<td><?php echo h($state); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__($binding), array('action' => 'setActive', $secureUser, $val), array('bind' => "click.setActive")); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $user['User']['id']), null, __('Are you sure you want to delete # %s?', $user['User']['id'])); ?>
		</td>
	</tr>
