<div class="shipments view">
<h2><?php echo __('Shipment'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Order'); ?></dt>
		<dd>
			<?php echo $this->Html->link($shipment['Order']['name'], array('controller' => 'orders', 'action' => 'view', $shipment['Order']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Carrier'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['carrier']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Method'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['method']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Tracking'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['tracking']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Status'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['status']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Carrier Notes'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['carrier_notes']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Shipment Cost'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['shipment_cost']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('First Name'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['first_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Last Name'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['last_name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Email'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['email']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Phone'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['phone']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Shipping Address'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['address']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Shipping Address2'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['address2']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Shipping City'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['city']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Shipping Zip'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['zip']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Shipping State'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['state']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Shipping Country'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['country']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Weight'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['weight']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Created'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['created']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Modified'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['modified']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Order Number'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['shipment_code']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('billing'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['billing']); ?>
			&nbsp;
		</dd>
			<dt><?php echo __('Billing Account'); ?></dt>
		<dd>
			<?php echo h($shipment['Shipment']['billing_account']); ?>
			&nbsp;
		</dd>
</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Shipment'), array('action' => 'edit', $shipment['Shipment']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Shipment'), array('action' => 'delete', $shipment['Shipment']['id']), null, __('Are you sure you want to delete # %s?', $shipment['Shipment']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Shipments'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Shipment'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?> </li>
	</ul>
</div>
