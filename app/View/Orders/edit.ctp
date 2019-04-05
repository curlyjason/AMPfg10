<?php
//debug($this->request->data);

echo $this->start('css');
echo $this->FgHtml->css('ampfg_forms');
echo $this->FgHtml->css('ampfg_grain');
echo $this->end('css');

echo $this->start('script');
echo $this->FgHtml->script('form');
echo $this->end('script');

$order = $this->request->data['Order'];
$shipment = $this->request->data['Shipment'][0];
$orderItem = $this->request->data['OrderItem'];

echo $this->Html->div('orderStatus', NULL);
echo $this->FgHtml->tag('h2', 'Order No: ' . $order['order_number']);
echo $this->FgHtml->tag('h2', 'Order Status: ' . $order['status']);
//echo $this->FgHtml->statusCheckbox($this->request->data);
echo '</div>';

echo $this->Form->create('Order', array(
    'class' => 'grainVersion'
));

echo $this->Html->div('billingAddress', NULL);
echo $this->FgHtml->tag('h2', 'Billing');
echo $this->FgHtml->decoratedTag('Company', 'p', $order['billing_company']);
echo $this->FgHtml->decoratedTag('Address', 'p', $order['billing_address']);
if(!empty($order['billing_address2'])){
echo $this->FgHtml->decoratedTag('Address 2', 'p', $order['billing_address2']);
}
$csz = $order['billing_city'] . ', ' . $order['billing_state'] . ' ' . $order['billing_zip'];
echo $this->FgHtml->decoratedTag('City, State ZIP', 'p', $csz);
echo $this->FgHtml->decoratedTag('Country', 'p', $order['billing_country']);
echo '</div>';

echo $this->Html->div('shippingAddress', NULL);
echo $this->FgHtml->tag('h2', 'Shipping Address');
echo $this->FgHtml->decoratedTag('Name', 'p', $shipment['first_name'] . ' ' . $shipment['last_name']);
echo $this->FgHtml->decoratedTag('Email', 'p', $shipment['email']);
echo $this->FgHtml->decoratedTag('Phone', 'p', $shipment['phone']);
echo $this->FgHtml->decoratedTag('Company', 'p', $shipment['company']);
echo $this->FgHtml->decoratedTag('Address', 'p', $shipment['address']);
if (!empty($shipment['address2'])) {
	echo $this->FgHtml->decoratedTag(' ', 'p', $shipment['address2']);
}
$shipCsz = $shipment['city'] . ', ' . $shipment['state'] . ' ' . $shipment['zip'];
echo $this->FgHtml->decoratedTag('City, State ZIP', 'p', $shipCsz);
echo $this->FgHtml->decoratedTag('Country', 'p', $shipment['country']);
echo $this->Form->input('Shipment.0.tracking', array('class' => 'form-control'));
echo $this->Form->input('Shipment.0.shipment_cost', array('class' => 'form-control'));
echo '</div>';

echo $this->Html->div('shippingMethod', NULL);
echo $this->FgHtml->tag('h2', 'Shipping Method');
echo $this->FgHtml->decoratedTag('Carrier', 'p', $shipment['carrier']);
echo $this->FgHtml->decoratedTag('Method', 'p', $shipment['method']);
echo '</div>';

echo '</div>'; //close shippingInputs div
echo '</div>'; //close addressSelectors div


$tabindex = 1;
$headerRow = array('#', 'ITEM', 'PRICE', 'QUANTITY', 'SUBTOTAL');
$i = 1;
foreach ($orderItem as $item):
    $cells[] = array(
        array($i++,
            array('class' => 'cartItem', 'id' => 'row-' . $item['id'])
        ),
        array($item['name'],
            array('class' => 'cartItem', 'id' => 'name-' . $item['id'])
        ),
        array($item['price'],
            array('class' => 'cartItem', 'id' => 'price-' . $item['id'])
        ),
        array($item['quantity'],
            array('class' => 'cartItem', 'id' => 'quantity-' . $item['id'])
        ),
        array($item['subtotal'],
            array('class' => 'cartItem', 'id' => 'subtotal-' . $item['id'])
        )
    );
endforeach;

echo('<table>');
echo $this->Html->tableHeaders($headerRow);
echo $this->Html->tableCells($cells);
echo('</table>');

echo $this->FgForm->button('Cancel', array(
    'type' => 'button',
    'bind' => 'click.hrefCancelButton',
	'href' => $this->request->referer(),
	'class' => 'big red'
));
echo $this->FgForm->button('Save', array(
    'type' => 'submit',
    'class' => 'big green'
	));

echo $this->Form->end();
?>
