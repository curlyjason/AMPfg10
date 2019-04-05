<?php
$this->start('jsGlobalVars');
    echo 'var method = ' . json_encode($method) . ';';
$this->end();

echo $this->start('css');
echo $this->FgHtml->css('ampfg_forms');
echo $this->FgHtml->css('ampfg_grain');
echo $this->FgHtml->css('cart');
echo $this->FgHtml->css('address');
echo $this->end('css');

echo $this->start('script');
echo $this->FgHtml->script('shop_address.js');
echo $this->end('script');


echo $this->Form->create('Order', array('class' => 'grainVersion', 'type' => 'file'));

echo $this->Html->div('hidden addressSection', NULL);
	echo $this->element('hidden_address', array($shop));
echo '</div>';//close hiddenAddresses
	
echo $this->Html->div('addressSection', NULL);
	echo $this->FgHtml->tag('h2', 'Billing Address', array('class' => 'toggle', 'id' => 'billingAddress'));
	echo $this->element('billing_address', array($billingAddress));
echo '</div>';//close billingAddressElement

echo $this->Html->div('addressSection', NULL);
	echo $this->FgHtml->tag('h2', 'Order References', array('class' => 'toggle', 'id' => 'referencesAddress'));
	echo $this->element('references_address');
echo '</div>';//close orderReference

echo $this->Html->div('addressSection', NULL);
	echo $this->FgHtml->tag('h2', 'Shipping Address', array('class' => 'toggle', 'id' => 'shippingAddress'));
	echo $this->element('shipping_address', array($myAddresses, $stateList, $countryList));
echo '</div>';//close shippingAddress

echo $this->Html->div('addressSection', NULL);
	echo $this->FgHtml->tag('h2', 'Shipping Method', array('class' => 'toggle', 'id' => 'shippingMethod'));
	echo $this->element('shipping_method', array($shipmentBillingOptions, $carrier, $method, $UPS, $FedEx, $Other, $thirdParty));
echo '</div>';//close shippingMethod

echo $this->Html->div('addressClosingButtons');
	echo $this->FgForm->button('Continue', array(
		'type' => 'submit',
		'class' => 'btn btn-default btn-primary addressSubmit',
		'bind' => 'click.prevalidateSubmit'));

	echo $this->FgForm->button('<- Cart', array(
		'type' => 'button',
		'bind' => 'click.addressCancel',
		'class' => 'addressCancel'
	));
echo '</div>';//close addressClosingButtons

echo $this->Form->end();
?>