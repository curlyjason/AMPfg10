<?php 
$this->start('jsGlobalVars');
echo 'var customer = "' . $this->request->data['customer'] . '";';
echo 'var active = "' . $this->request->data['active'] . '";';
$this->end();

$this->start('script');
echo $this->Html->script('inactive');
$this->end();

$this->start('css');
echo $this->Html->css('inactive');
$this->end();
?>

<div class="catalogs index">
	<h2><?php echo __('Catalogs'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('customer_name'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('active'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php 
	foreach ($catalogs as $catalog):
		
	echo $this->element('Catalog/inactive_row', array('catalog' => $catalog));
	?>
	
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="sidebarInstructions actions">
	<?php
	echo $this->Form->input('customers', array(
		'type' => 'select',
		'empty' => 'Select a customer',
		'options' => $customers,
		'bind' => 'change.customerFilter',
		'value' => $this->request->data['customer']
	));
	echo $this->Form->radio('active', 
			array(
				'inactive' => 'Inactive', 
				'active' => 'Active', 
				'all' => 'All'), 
			array(
				'value' => $this->request->data['active'],
				'bind' => 'change.stateFilter')
			);
	echo $this->FgForm->input('paginationLimit', array(
		'type' => 'select',
		'options' => array(
			'1' => '1', '5' => '5', '10' => '10', '25' => '25', '50' => '50'),
		'empty' => 'Items/pg',
		'label' => false,
		'bind' => 'change.limit'
	));
	
		echo $this->Html->tag('h4', 'Catalog Product Activation');
		echo $this->Html->tag('ul', NULL);
		echo $this->Html->tag('li',"The products listed here are all inactive.");
		echo $this->Html->tag('li',"You can reactivate them or delete them.");
		echo $this->Html->tag('li',"Folder type products often contain 'child' products.");
		echo $this->Html->tag('li',"If you deactivate a folder prouduct you deactivate all of the contained 'child' products.");
		echo '</ul>';
		echo $this->Html->tag('h4', 'Items & Products');
		echo $this->Html->tag('ul', NULL);
		echo $this->Html->tag('li',"Remember that all products are based upon the inventoried Items they represent.");
		echo $this->Html->tag('li',"If you deactivate a single product, you may not be deactiving its inventoried Item.");
		echo '</ul>';
	?>
</div>