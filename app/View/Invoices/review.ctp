<?php

echo $this->start('css');
echo $this->Html->css('ampfg_grain');
echo $this->Html->css('cart');
echo $this->end('css');

echo $this->start('script');
echo $this->Html->script('invoice.js');
echo $this->end('script');

echo $this->Html->div('invoiceReview', NULL);
	echo $this->Html->div('reviewBillingAddress', NULL);
		echo $this->FgHtml->decoratedTag('Job Number', 'p', $invoice['Order']['job_number']);
		echo $this->FgHtml->decoratedTag('Customer', 'p', $invoice['Customer']['name']);
		echo $this->FgHtml->decoratedTag('Date', 'p', $this->Time->format($invoice['Order']['creation'], '%b %d, %Y'));
		echo $this->Html->div('orderSummary', NULL);
			echo $this->Html->tag('h2', 'Order Summary');
			echo $this->FgHtml->decoratedTag('Total Items: ', 'p', $invoice['Order']['order_item_count']);
			echo $this->FgHtml->decoratedTag('Handling: ', 'p', $invoice['Order']['handling']);
			echo $this->FgHtml->decoratedTag('Total ', 'p', $invoice['Order']['total']);
			echo $this->FgHtml->decoratedTag('Notes', 'p', $this->FgHtml->markdown($invoice['Order']['note']));
		echo '</div>';//close orderSummary div
	echo '</div>';//close billingAddress div

	echo $this->Html->div('reviewOrderItems', NULL);
		echo $this->Html->tag('h2', 'Order Items');

		$headerRow = array('#', 'Item', 'Price', 'Quantity', 'Subtotal');

		foreach ($invoice['OrderItem'] as $item):
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
		echo $this->Form->button('Confirm Order', array('type' => 'submit', 'class' => 'orderReviewSubmitButton', 'ecape' => false));
		echo $this->Form->button('<- Address', array('class' => 'orderReviewBackButton', 'ecape' => false, 'type' => 'button', 'bind' => 'click.reviewBackButton'));
	echo '</div>';//close buttons div
	echo $this->Form->end();
echo '</div>'; //end orderReview div
?>
