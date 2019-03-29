<?php 
	if ($catalog['Catalog']['active']) {
		$state = 'Active';
		$binding = 'Inactivate';
		$val = 0;
	} else {
		$state = 'Inactive';
		$binding = 'Activate';
		$val = 1;
	}
?>
	<tr id="<?php echo "row-{$catalog['Catalog']['id']}" ?>">
		<td><?php echo h($catalog['Catalog']['customer_name']); ?>&nbsp;</td>
		<td><?php echo h($catalog['Catalog']['name']); ?>&nbsp;</td>
		<td><?php echo h($state); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__($binding), array('action' => 'setActive', $catalog['Catalog']['id'], $val), array('bind' => "click.setActive")); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $catalog['Catalog']['id']), null, __('Are you sure you want to delete # %s?', $catalog['Catalog']['id'])); ?>
			<?php if($catalog['Catalog']['type'] & FOLDER || $catalog['Catalog']['type'] & KIT){echo $this->Html->image('folder.png', array('class' => 'folder'));} else {echo $this->Html->image('transparent.png', array('class' => 'folder'));}?>
		</td>
	</tr>
