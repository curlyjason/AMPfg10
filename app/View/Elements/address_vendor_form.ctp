<div class="addressVendorForm">
    <?php
    echo $this->Session->flash();
    echo $this->FgForm->create('Address', array('class' => 'grainVersion'));
    if (!empty($this->request->data) && isset($this->request->data['Address']['id'])) {
		echo $this->FgForm->secureId('Address.id', $this->request->data['Address']['id']);
    }
    echo $this->FgForm->input('Address.user_id', array(
		'type' => 'hidden'
    ));
    echo $this->FgForm->input('Address.type', array(
		'type' => 'hidden',
        'value' => 'vendor'
    ));
    echo $this->FgForm->input('Address.epms_vendor_id', array(
        'type' => 'text',
		'label' => 'EPMS Vendor ID'
    ));
    echo $this->FgForm->input('Address.name');
    echo $this->FgForm->input('Address.address');
    echo $this->FgForm->input('Address.address2');
    echo $this->FgForm->input('Address.city');
	echo $this->FgForm->stateInput('Address',$stateList);
    echo $this->FgForm->input('Address.zip', array('label' => 'Postal code'));
	echo $this->FgForm->countryInput('Address',$countryList);
    echo $this->FgForm->activeRadio('Address');
    
    echo $this->FgHtml->tag('h2', 'Billing contact information');
    echo $this->FgForm->input('Address.first_name');
    echo $this->FgForm->input('Address.last_name');
    echo $this->FgForm->input('Address.email');
    echo $this->FgForm->input('Address.phone');
    
    echo $this->FgForm->button('Cancel', array(
		'type' => 'button',
		'bind' => 'click.basicCancelButton'
    ));
    echo $this->FgForm->button('Submit', array(
		'type' => 'submit',
		'class' => 'submitButton'
    ));
    echo $this->FgForm->end();
    ?>
</div>
