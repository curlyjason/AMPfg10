<?php
echo $this->FgHtml->div('billingAddress', NULL, array('bind' => 'validate.validateBillingAddress'));
	$address = $billingAddress['Address'];
	echo $this->FgHtml->decoratedTag('Customer Name', 'p', $billingAddress['Customer']['name']);
	echo $this->FgHtml->decoratedTag('Address', 'p', $address['address']);
	if(!empty($address['address2'])){
		echo $this->FgHtml->decoratedTag('Address 2', 'p', $address['address2']);    
	}
	//$csz = $address['city'] . ', ' . $address['state'] . ' ' . $address['zip'];
	echo $this->FgHtml->decoratedTag('City, State ZIP', 'p', $address['csz']);
	if(!empty($address['country'])){
		echo $this->FgHtml->decoratedTag('Country', 'p', $address['country']);    
	}
	echo $this->Form->input('Order.user_customer_id', array(
		'class' => 'form-control',
		'type' => 'hidden',
		'value' => $billingAddress['Customer']['user_id']));
	echo $this->Form->input('Order.billing_company', array(
		'class' => 'form-control',
		'type' => 'hidden',
		'value' => $billingAddress['Customer']['name']));
	echo $this->Form->input('Order.first_name', array(
		'class' => 'form-control',
		'type' => 'hidden',
		'value' => $this->Session->read('Auth.User.first_name')));
	echo $this->Form->input('Order.last_name', array(
		'class' => 'form-control',
		'type' => 'hidden',
		'value' => $this->Session->read('Auth.User.last_name')));
	echo $this->Form->input('Order.phone', array(
		'class' => 'form-control',
		'type' => 'hidden',
		'value' => $address['phone']));
	echo $this->Form->input('Order.email', array(
		'class' => 'form-control',
		'type' => 'hidden',
		'value' => $this->Session->read('Auth.User.username')));
	echo $this->Form->input('Order.billing_address', array(
		'class' => 'form-control',
		'type' => 'hidden',
		'value' => $address['address']));
	echo $this->Form->input('Order.billing_address2', array(
		'class' => 'form-control',
		'type' => 'hidden',
		'value' => $address['address2']));
	echo $this->Form->input('Order.billing_city', array(
		'class' => 'form-control',
		'type' => 'hidden',
		'value' => $address['city']));
	echo $this->Form->input('Order.billing_state', array(
		'class' => 'form-control',
		'type' => 'hidden',
		'value' => $address['state']));
	echo $this->Form->input('Order.billing_zip', array(
		'class' => 'form-control',
		'type' => 'hidden',
		'value' => $address['zip']));
	echo $this->Form->input('Order.billing_country', array(
		'class' => 'form-control',
		'type' => 'hidden',
		'value' => $address['country']));
	echo $this->Form->input('Order.taxable', array(
		'class' => 'form-control',
		'type' => 'hidden',
		'value' => $billingAddress['Customer']['taxable']));
	echo $this->Form->input('Order.fedex_acct', array(
		'class' => 'form-control',
		'type' => 'hidden',
		'value' => $address['fedex_acct']));
	echo $this->Form->input('Order.ups_acct', array(
		'class' => 'form-control',
		'type' => 'hidden',
		'value' => $address['ups_acct']));
	
	echo $this->element('next_address');
echo '</div>';