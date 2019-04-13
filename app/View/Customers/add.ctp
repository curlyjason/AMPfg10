<?php
echo $this->Html->div('customers'); // Opening customer form div

echo $this->Form->create('Customer', array('class' => 'grainVersion', 'type' => 'file'));// Open Form

echo $this->element('edit_customer_inputs', array(
    'data' => $tax_rate_id
));

//Form submission & Cancel buttons
echo $this->FgForm->button('Cancel', array(
    'type' => 'button',
    'bind' => 'click.basicCancelButton'
));
echo $this->FgForm->button('Submit', array(
    'type' => 'submit',
    'class' => 'submitButton'
));


echo '</div>'; // close customer form div
?>