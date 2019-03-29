<div class="addressForm">
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
	'type' => 'hidden'
    ));
    echo $this->FgForm->input('Address.name', array('label' => 'Address Name'));
    echo $this->FgForm->input('Address.first_name', array('label' => 'Contact First Name'));
    echo $this->FgForm->input('Address.last_name', array('label' => 'Contact Last Name'));
    echo $this->FgForm->input('Address.email', array('label' => 'Contact email', 'type' => 'text'));
    echo $this->FgForm->input('Address.phone', array('label' => 'Contact Phone'));
    echo $this->FgForm->input('Address.company');
    echo $this->FgForm->input('Address.address');
    echo $this->FgForm->input('Address.address2');
    echo $this->FgForm->input('Address.city');
	echo $this->FgForm->stateInput('Address',$stateList);
    echo $this->FgForm->input('Address.zip', array('label' => 'Postal code'));
	echo $this->FgForm->countryInput('Address',$countryList);
    echo $this->FgForm->activeRadio('Address');
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
