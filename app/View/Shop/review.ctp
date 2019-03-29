<?php

echo $this->start('css');
echo $this->Html->css('ampfg_grain');
echo $this->Html->css('ampfg_forms');
echo $this->Html->css('shop_review');
echo $this->end('css');

echo $this->start('script');
echo $this->Html->script('shop_review.js');
echo $this->end('script');

echo $this->Html->div('orderReview', NULL);
	echo $this->Html->div('reviewAddresses', NULL);
		echo $this->Html->div('reviewBillingAddress', NULL);
			echo $this->Html->tag('h2', 'Billing');
			echo $this->FgHtml->decoratedTag('Company', 'p', $shop['Order']['billing_company']);
			echo $this->FgHtml->decoratedTag('Address', 'p', $shop['Order']['billing_address']);
			if(!empty($shop['Order']['billing_address2'])){
			echo $this->FgHtml->decoratedTag('Address 2', 'p', $shop['Order']['billing_address2']);
			}
			$csz = $shop['Order']['billing_city'] . ', ' . $shop['Order']['billing_state'] . ' ' . $shop['Order']['billing_zip'];
			echo $this->FgHtml->decoratedTag('City, State ZIP', 'p', $csz);
			echo $this->FgHtml->decoratedTag('Country', 'p', $shop['Order']['billing_country']);
			echo $this->Html->div('orderSummary', NULL);
				echo $this->Html->tag('h2', 'Order Summary');
				echo $this->FgHtml->decoratedTag('Reference #: ', 'p', $shop['Order']['order_reference']);
				echo $this->FgHtml->decoratedTag('Total Items: ', 'p', $shop['Order']['order_item_count']);
				echo $this->FgHtml->decoratedTag('Handling: ', 'p', $shop['Order']['handling']);
				echo $this->FgHtml->decoratedTag('Total ', 'p', $shop['Order']['total']);
				echo $this->FgHtml->decoratedTag('Notes', 'p', $this->FgHtml->markdown($shop['Order']['note']));
			echo '</div>';//close orderSummary div
		echo '</div>';//close billingAddress div
		
		echo $this->Html->div('reviewShippingAddress', NULL);
			echo $this->Html->tag('h2', 'Shipping');
			echo $this->FgHtml->decoratedTag('Name', 'p', $shop['Shipment']['first_name']. ' ' . $shop['Shipment']['last_name']);
			echo $this->FgHtml->decoratedTag('Email', 'p', $shop['Shipment']['email']);
			echo $this->FgHtml->decoratedTag('Phone', 'p', $shop['Shipment']['phone']);
			echo $this->FgHtml->decoratedTag('Company', 'p', $shop['Shipment']['company']);
			echo $this->FgHtml->decoratedTag('Address', 'p', $shop['Shipment']['address']);
			if(!empty($shop['Shipment']['address2'])){
			echo $this->FgHtml->decoratedTag('Address 2', 'p', $shop['Shipment']['address2']);
			}
			$csz = $shop['Shipment']['city'] . ', ' . $shop['Shipment']['state'] . ' ' . $shop['Shipment']['zip'];
			echo $this->FgHtml->decoratedTag('City, State ZIP', 'p', $csz);
			echo $this->FgHtml->decoratedTag('Country', 'p', $shop['Shipment']['country']);
			echo $this->FgHtml->decoratedTag('Carrier', 'p', $shop['Shipment']['carrier']);
			echo $this->FgHtml->decoratedTag('Delivery Type', 'p', ($shop['Shipment']['residence']) ? 'Home Delivery' : 'Delivery to a business');
			$displayMethod = $displayMethods[$shop['Shipment']['carrier']][$shop['Shipment']['method']];
			echo $this->FgHtml->decoratedTag('Method', 'p', $displayMethod);
			echo $this->FgHtml->decoratedTag('Billing', 'p', $shop['Shipment']['billing']);
		echo '</div>';//close shippingAddress div
	echo '</div>';//close addresses div
	
	echo $this->element('Doc/doc_list');

	echo $this->Html->div('reviewOrderItems', NULL);
		echo $this->Html->tag('h2', 'Order Items');

		$headerRow = array('#', 'Item', 'Price', 'Quantity', 'Subtotal');

		foreach ($shop['OrderItem'] as $item):
			if (isset($item['Image'][0]['img_file'])) {
				$image = $this->Html->image('image' . DS . 'img_file' . DS . $item['Image'][0]['id'] . DS . 'x160y120_' . $item['Image'][0]['img_file']);
			} else {
				$image = '';
			}
			$cells[] = array(
				array($image,
					array('class' => 'cartItem', 'id' => 'row-' . $item['Item']['id'])
				),
				array($this->Html->link($item['Catalog']['name'], array('controller' => 'catalogs', 'action' => 'item_peek', $item['Catalog']['id'])).'<div></div>',
					array('class' => 'cartItem')
				),
				array($item['Item']['price'],
					array('class' => 'cartItem', 'id' => 'price-' . $item['Item']['id'])
				),
				array($item['quantity'] . ' ' . $this->FgHtml->unitName($item, 'Order') . '<p class="hint">' . $this->FgHtml->calculatedQuantity($item, 'Order') . '</p>',
					array('class' => 'cartItem', 'id' => 'quantity-' . $item['Item']['id'])
				),
				array($item['subtotal'],
					array('class' => 'cartItem', 'id' => 'subtotal-' . $item['Item']['id'])
				)
			);
		endforeach;

		echo('<table>');
		echo $this->Html->tableHeaders($headerRow);
		echo $this->Html->tableCells($cells);
		echo('</table>');
	echo '</div>';//close orderItems div
	
		echo $this->Form->create('Order');

	echo $this->Html->div('reviewClosingButtons', NULL);
		echo $this->Form->button('Confirm Order', array('type' => 'submit', 'class' => 'orderReviewSubmitButton big green', 'ecape' => false));
		echo $this->Form->button('<- Address', array('class' => 'orderReviewBackButton big red', 'ecape' => false, 'type' => 'button', 'bind' => 'click.reviewBackButton'));
	echo '</div>';//close buttons div
	echo $this->Form->end();
echo '</div>'; //end orderReview div
?>
