<?php
echo $this->set('title_for_layout', 'Shopping Cart');

echo $this->Html->script(array('cart.js'), array('inline' => false));
$this->start('css');
echo $this->Html->css('cart');
$this->end();

$summary =  $this->Html->para(null, 'Order Total: ' . $this->Html->tag('span', $shop['Order']['total'], array('class' => 'red', 'id' => 'total')));
//		. $this->FgForm->input('Order.order_reference', array('value' => isset($shop['Order']['order_reference']) ? $shop['Order']['order_reference'] : ''));



	if (empty($shop['OrderItem'])){

    echo $this->Html->tag('h2','Shopping Cart is empty');
	
	}else{

    echo $this->Form->create(NULL, array('url' => array('controller' => 'shop', 'action' => 'cartupdate')));

    $tabindex = 1;
    $headerRow = array('#', 'Item', 'Price', 'Available', 'Quantity', 'SubTotal', '');
    foreach ($shop['OrderItem'] as $product):
        if (isset($product['Image'][0]['img_file'])) {
            $image = $this->Html->image('image' . DS . 'img_file' . DS . $product['Image'][0]['id'] . DS . 'x160y120_' . $product['Image'][0]['img_file']);
        } else {
            $image = '';
        }

//		$this->FgHtml->ddd($product);
		$price = $this->Number->currency($product['Catalog']['price'], 'USD');
        $subtotal = $this->Number->currency($product['subtotal'], 'USD');
//        $availableClass = ($product['Item']['available_qty'] < 0) ? 'cartItem overCommitted' : 'cartItem';
        $cells[] = array(
            array($image,
                array('class' => 'cartItem', 'id' => 'row-' . $product['Catalog']['id'])
            ),
            array($this->Html->link($product['Catalog']['name'], array('controller' => 'catalogs', 'action' => 'view', $product['Catalog']['id'])),
                array('class' => 'cartItem')
            ),
            array($price,
                array('class' => 'cartItem', 'id' => 'price-' . $product['Catalog']['id'])
            ),
            array(str_replace('Available: ', '', $this->FgHtml->calculatedQuantity($product, 'Catalog', FALSE)),
                array('class' => 'cartItem', 'id' => 'available-'. $product['Catalog']['id'])
            ),
            array($this->Form->input('quantity-' . $product['Catalog']['id'], array(
				'div' => false,
				'class' => 'numeric form-control input-small',
				'label' => false,
				'size' => 2,
				'tabindex' => $tabindex++,
				'data-id' => $product['Catalog']['id'],
				'value' => $product['quantity'],
				'after' => $this->FGHtml->itemLimitAlert($product, $itemLimitBudget))),
                array('class' => 'cartItem', 'id' => 'quantity-' . $product['Catalog']['id'])
            ),
            array($subtotal,
                array('class' => 'cartItem', 'id' => 'subtotal-' . $product['Catalog']['id'])
            ),
            array($this->Html->tag('span', /* $this->Html->image('icon-remove') */ '', array('class' => 'remove', 'id' => '' . $product['Catalog']['id'])),
                array('class' => 'cartItem')
            )
        );
    endforeach;

    // Add the cart wide modification tools
    $cells[] = array(
        array('', array('colspan' => '4', 'class' => 'summary')),
        array($this->FgForm->button('Recalculate', array('type' => 'button','class' => 'recalculate')). ' ' . 
			$this->FgForm->button('Clear Cart', array('type' => 'button', 'class' => 'clearCart')), array('class' => 'summary', 'colspan' => '3'))
    );

    // Add the cart wide checkout and summary tools
    $cells[] = array(
//        array($this->FgForm->input('Order.note', array('label' => 'Order Note', 'value' => $shop['Order']['note'])), array('colspan' => '4', 'class' => 'summary')),
        array('', array('colspan' => '4', 'class' => 'summary')),
        array($summary, array('colspan' => '3', 'class' => 'summary'))
    );
	
	//Add the checkout buttons
	$checkoutButtons = array($this->FgForm->button('Checkout', array('type' => 'button', 'class' => 'checkout', 'colspan' => '2')), array('class' => 'summary', 'colspan' => '2'));
	$cells[] = array(array('', array('colspan' => '5', 'class' => 'checkoutCell summary')),$checkoutButtons);
	
//	debug($cells);
    echo('<table>');
    echo $this->Html->tableHeaders($headerRow);
    echo $this->Html->tableCells($cells);
    echo('</table>');
   echo $this->Form->end(); 
}
?>
