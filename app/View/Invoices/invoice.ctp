<?php
    echo $this->FgForm->create();
    echo $this->FgForm->input('customers', array(
            'type' => 'select',
            'empty' => 'Select a customer',
            'options' => $customers
        ));
    echo $this->FgForm->submit('Invoice');
    echo $this->FgForm->end();
?>