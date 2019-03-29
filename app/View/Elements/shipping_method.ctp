<?php
echo $this->FgHtml->div('shippingMethod hide', NULL, array('bind' => 'validate.validateShippingMethod'));

	echo $this->FgHtml->link('Set as default shipping for this company', array('controller' => 'users', 'action' => 'defaultShipping'), array('id' => 'shippingDefault'));

	echo $this->FgForm->input ('Shipment.billing', array(
		'class' => 'form-control',
		'options' => $shipmentBillingOptions,
		'bind' => 'mouseup.shippingBilling'));
	echo $this->FgForm->input ('Shipment.carrier', array(
		'class' => 'form-control',
		'empty' => 'Select a Carrier',
		'options' => $carrier));
	echo $this->FgForm->input ('Shipment.method', array(
		'class' => 'form-control',
		'empty' => 'Select a Method',
		'options' => $method));
	echo $this->FgForm->input ('UPS', array(
		'class' => 'form-control hide',
		'empty' => 'Select a Method',
		'options' => $UPS,
		'div' => false,
		'label' => false));
	echo $this->FgForm->input ('FedEx', array(
		'class' => 'form-control hide',
		'empty' => 'Select a Method',
		'options' => $FedEx,
		'div' => false,
		'label' => false));
	echo $this->FgForm->input ('Other', array(
		'class' => 'form-control hide',
		'empty' => 'Select a Method',
		'options' => $Other,
		'div' => false,
		'label' => false));
	echo $this->FgHtml->div ('thirdParty hide', NULL);
		echo $this->FgForm->input ('Shipment.billing_account', array ('label' => 'Acct', 'class' => 'form-control'));
		echo $this->FgHtml->div ('tpbAddress hide', NULL);
			echo $this->Form->input('Shipment.tpb_selector', array(
				'type' => 'select',
				'options' => $thirdParty,
				'empty' => 'Choose a third party biller',
				'label' => 'Third Party Acct',
				'bind' => 'change.thirdPartyBillingSelector'
			));
			echo $this->FgForm->input ('Shipment.tpb_company', array('label' => 'tpbCompany'));
			echo $this->FgForm->input ('Shipment.tpb_address', array('label' => 'tpbAddress'));
			echo $this->FgForm->input ('Shipment.tpb_city', array('label' => 'tpbCity'));
			echo $this->FgForm->input ('Shipment.tpb_state', array('label' => 'tpbState'));
			echo $this->FgForm->input ('Shipment.tpb_zip', array('label' => 'tpbZIP'));
			echo $this->FgForm->input ('Shipment.tpb_phone', array('label' => 'tpbPhone'));
		echo '</div>';//close tpbAddress div
	echo '</div>';//chose thirdParty div
echo '</div>'; //close shippingMethod
