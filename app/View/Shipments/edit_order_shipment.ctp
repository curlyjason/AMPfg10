<?php
$this->start('jsGlobalVars');
    echo 'var method = ' . json_encode($method) . ';';
$this->end();

echo $this->Form->create('Shipment', array(
    'class' => 'grainVersion'
));
//======Hidden Inputs
echo $this->FgForm->input('Shipment.id', array('type' => 'hidden'));
echo $this->FgForm->input('Shipment.order_id', array('type' => 'hidden'));

echo $this->Html->div('displayAddresses', NULL);
	echo $this->Html->div('billingAddress', NULL);
		echo $this->Html->div('shippingMethod', NULL);
			echo $this->FgHtml->tag('h2', 'Shipping Method');
			echo $this->FgForm->input ('Shipment.billing', array(
				'class' => 'form-control',
//				'value' => 'Sender',
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
            echo $this->Html->div ('thirdParty hide', NULL);
                echo $this->FgForm->input ('Shipment.billing_account', array ('label' => 'Acct', 'class' => 'form-control'));
                echo $this->Html->div ('tpbAddress hide', NULL);
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
		echo '</div>';//close shippingMethod div
        if($this->request->data['Order']['status'] == 'Shipped' || $this->request->data['Order']['status'] == 'Shipping'){
            echo $this->Form->input('Shipment.tracking', array('label' => 'Tracking Number'));
            echo $this->Form->input('Shipment.shipment_cost', array('label' => 'Shipment Cost'));
        }
		echo $this->Html->div('addressClosingButtons');
			echo $this->FgForm->button('Save', array(
				'type' => 'submit',
				'bind' => 'click.saveShipment',
				'class' => 'btn btn-default btn-primary addressSubmit',
				'orderLink' => $this->request->data['Shipment']['order_id']
			));

			echo $this->FgForm->button('Cancel', array(
				'type' => 'button',
				'bind' => 'click.addressEditCancel',
				'class' => 'addressEditCancel'
			));
		echo '</div>';//close addressClosingButtons
	echo '</div>';//close billing address div

	echo $this->Html->div('addressSelectors', NULL);
		echo $this->FgHtml->tag('h2', 'Shipping Address');
		echo $this->Session->flash('validationError');
		if(!empty($myAddresses)){
			echo $this->Form->input('myAddresses', array('empty' => 'Select an Address', 'class' => 'addressSelect'));
		}
		echo $this->Form->input('connectedAddresses', array('empty' => 'Select an Address', 'class' => 'addressSelect'));

		echo $this->Html->div('shippingInputs', NULL);
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
			echo $this->FgForm->folderCheck($model = 'Shipment', $field = 'save_to_my_address_book');
		echo '</div>'; //close shippingInputs div
	echo '</div>'; //close addressSelectors div
echo '</div>'; //close displayAddresses div

echo $this->Form->end();
?>