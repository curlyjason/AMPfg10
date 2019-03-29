<div class="customerDisplay">
    <?php
?>
</div>
<div class="userForm">
    <?php
    echo $this->Session->flash();
    echo $this->FgForm->create('Customer', array('class' => 'grainVersion'));
    echo $this->FgForm->secureId('Customer.id', $id);
    echo $this->FgForm->input('Customer.order_contact');
    echo $this->FgForm->input('Customer.billing_contact');
    
    //the tinyint ticker set
    echo $this->FgForm->folderCheck('Customer', 'allow_backorder', array('label' => 'Allow customer to backorder items'));
    echo $this->FgForm->folderCheck('Customer', 'allow_direct_pay', array('label' => 'Allow customer to pay directly for items'));
    echo $this->FgForm->folderCheck('Customer', 'release_hold', array('label' => 'Require staff member to release all orders'));
//    echo $this->FgForm->folderCheck('Customer', 'taxable', array('label' => 'Check if customer pays sales tax'));

    echo $this->FgForm->button('Submit', array(
	'type' => 'submit',
	'class' => 'submitButton'
    ));
    echo $this->Form->end();
    ?>
</div>