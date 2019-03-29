<?php
echo $this->FgHtml->div('shippingAddress hide', NULL, array('bind' => 'validate.validateShippingAddress'));
	echo $this->Session->flash('validationError');
    echo $this->Form->button('Clear Address', array('type' => 'button', 'class' => 'regular green', 'bind' => 'click.clearAddress'));
	echo $this->Form->input('sameaddress', array('type' => 'checkbox', 'label' => 'Copy billing address to shipping'));
	if(!empty($myAddresses)){
		echo $this->Form->input('myAddresses', array('empty' => 'Select an Address', 'class' => 'addressSelect'));
	}
	echo $this->Form->input('connectedAddresses', array('empty' => 'Select an Address', 'class' => 'addressSelect'));

	echo $this->FgHtml->div('shippingInputs', NULL);
		echo $this->Form->input('Shipment.first_name', array('class' => 'form-control', 'required' => true));
		echo $this->Form->input('Shipment.last_name', array('class' => 'form-control'));
		echo $this->Form->input('Shipment.email', array('type' => 'text', 'class' => 'form-control', 'placeholder' => "you@you.com, me@me.com, them@them.com"));
		echo $this->Form->input('Shipment.phone', array('class' => 'form-control'));
		echo $this->Form->input('Shipment.residence', array('type' => 'checkbox', 'class' => 'form-control'));
		echo $this->Form->input('Shipment.company', array('class' => 'form-control'));
		echo $this->Form->input('Shipment.address', array('class' => 'form-control'));
		echo $this->Form->input('Shipment.address2', array('class' => 'form-control'));
		echo $this->Form->input('Shipment.city', array('class' => 'form-control'));
		echo $this->FgForm->stateInput('Shipment',$stateList);
		echo $this->Form->input('Shipment.zip', array('class' => 'form-control'));
		echo $this->Form->input('Shipment.fedex_acct', array('class' => 'form-control', 'type' => 'hidden'));
		echo $this->Form->input('Shipment.ups_acct', array('class' => 'form-control', 'type' => 'hidden'));
		echo $this->FgForm->countryInput('Shipment',$countryList);
		echo $this->FgForm->folderCheck('Shipment', 'save_to_my_address_book');
	echo '</div>'; //close shippingInputs div
	echo $this->element('next_address');
echo '</div>'; //close shippingAddress div
