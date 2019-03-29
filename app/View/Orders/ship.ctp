<?php
//$this->start('jsGlobalVars');
//    echo 'var method = ' . json_encode($method) . ';';
//$this->end();

echo $this->start('css');
echo $this->FgHtml->css('ampfg_forms');
echo $this->FgHtml->css('ampfg_grain');
echo $this->end('css');

//echo $this->start('script');
////echo $this->FgHtml->script('shop_address.js');
//echo $this->FgHtml->script('shipment.js');
//echo $this->end('script');

$shipment = $this->request->data['Shipment'];

echo $this->FgHtml->div('orderNumber', NULL);
	echo $this->FgHtml->tag('h2', 'Order');
	echo $this->FgHtml->decoratedTag('Order No.', 'p', $shipment['shipment_code']);
echo '</div>';

echo $this->FgHtml->div('shipmentContact', NULL);
	echo $this->FgHtml->tag('h2', 'Contact');
	echo $this->FgHtml->decoratedTag('Name', 'p', $shipment['first_name']. ' ' . $shipment['last_name']);
	echo $this->FgHtml->decoratedTag('Email', 'p', $shipment['email']);
	echo $this->FgHtml->decoratedTag('Phone', 'p', $shipment['phone']);
echo '</div>';

echo $this->FgHtml->div('shippingAddress', NULL);
	echo $this->FgHtml->tag('h2', 'Shipping');
	echo $this->FgHtml->decoratedTag('Company', 'p', $shipment['company'], array('class' => 'decoration ShipmentCompany'));
	echo $this->FgHtml->decoratedTag('Address', 'p', $shipment['address'], array('class' => 'decoration AddressCompany'));
	if(!empty($shipment['address2'])){
		echo $this->FgHtml->decoratedTag('Address 2', 'p', $shipment['address2'], array('class' => 'decoration Address2Company'));
	}
	$csz = $shipment['city'] . ', ' . $shipment['state'] . ' ' . $shipment['zip'];
	echo $this->FgHtml->decoratedTag('City, State ZIP', 'p', $csz);

	echo $this->FgHtml->decoratedTag('Shipment.tax_rate_id', 'p', $shipment['tax_rate_id']);
	echo $this->FgHtml->decoratedTag('Country', 'p', $shipment['country']);
	echo $this->FgHtml->decoratedTag('Carrier', 'p', $shipment['carrier']);
	echo $this->FgHtml->decoratedTag('Method', 'p', $shipment['method']);
echo '</div>';

echo $this->Form->create('Shipment', array('class' => 'grainVersion'));
	echo $this->FgForm->input('Shipment.id', array('type' => 'hidden'));
	echo $this->FgForm->input('Shipment.order_id', array('type' => 'hidden'));

	echo $this->FgHtml->div('shipmentAddOns', NULL);
		echo $this->FgHtml->tag('h2', 'Cost & Tracking');
		echo $this->Form->input('Shipment.shipment_cost', array('class' => 'form-control', 'label' => 'Shipping Charge'));
		echo $this->Form->input('Shipment.tracking', array('class' => 'form-control', 'label' => 'Tracking No.'));
	echo '</div>';

	echo $this->FgForm->button('Continue', array(
		'type' => 'submit',
		'class' => 'btn btn-default btn-primary addressSubmit'
		));

	echo $this->FgForm->button('Cancel', array(
		'type' => 'button',
		'bind' => 'click.basicCancelButton'
	));
echo $this->Form->end();
?>