<?php
echo $this->Html->div('hiddenAddresses hide', NULL);
	echo $this->FgForm->input('Order.order_item_count', array('type' => 'hidden'));
	echo $this->FgForm->input('Order.quantity', array('type' => 'hidden'));
	echo $this->FgForm->input('Order.weight', array('type' => 'hidden'));
	echo $this->FgForm->input('Order.subtotal', array('type' => 'hidden'));
	echo $this->FgForm->input('Order.handling', array('type' => 'hidden'));
	echo $this->FgForm->input('Order.total', array('type' => 'hidden'));
	echo $this->FgForm->input('Order.shop', array('type' => 'hidden'));
	echo $this->FgForm->input('Order.selectedAddress', array('type' => 'hidden'));
	echo $this->FgForm->input('Order.selectedAddressSource', array('type' => 'hidden'));
	echo $this->FgForm->input('Order.access', array('type' => 'hidden', 'value' => $this->Session->read('Auth.User.access')));
//	echo $this->FgForm->input('Order.note', array('type' => 'hidden', 'value' => $shop['Order']['note']));
//	echo $this->FgForm->input('Order.order_reference', array('type' => 'hidden', 'value' => $shop['Order']['order_reference']));
echo '</div>';