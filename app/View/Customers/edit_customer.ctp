<!-- Customer/edit_customer -->
	<?php
//Div and form opening
echo $this->Html->div('customers'); // Opening customer form div
echo $this->Form->create('Customer', array('class' => 'grainVersion', 'type' => 'file')); // Open Form

//this element contains all of the input fields for this form
echo $this->element('edit_customer_inputs', array('data' => $tax_rate_id, 'customer_type' => $customer_type));

//Form submission & Cancel buttons
echo $this->FgForm->button('Cancel', array(
    'type' => 'button',
    'bind' => 'click.basicCancelButton'
));
echo $this->FgForm->button('Submit', array(
    'type' => 'submit',
    'class' => 'submitButton'
));

//Form and Div closing
echo $this->Form->end();
echo '</div>'; // close customer form div
?>
<!-- END Customer/edit_customer -->
