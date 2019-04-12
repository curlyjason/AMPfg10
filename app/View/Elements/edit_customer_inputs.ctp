<?php

//Setup basic variables
$userId = $this->Session->read('Auth.User.id');
//setup image

/**
 * @todo Build a helper class for creating these image strings
 */
if (isset($this->request->data['Customer']['Logo'][0]['img_file'])) {
	$image = $this->Html->image('image' . DS . 'img_file' . DS . $this->request->data['Customer']['Logo'][0]['id'] . DS . 'x160y120_' . $this->request->data['Customer']['Logo'][0]['img_file'], array('id' => 'ajaxEditImage'));
} else {
	$image = $this->Html->image('image' . DS . 'img_file' . DS . 'no' . DS . 'x160y120_' . 'image.jpg', array('id' => 'ajaxEditImage'));
};


echo $this->Html->tag('fieldset'); // Opening fieldset

echo $this->Html->tag('legend', __('Edit Customer'));

echo $this->Form->input('customer_code', array('label' => 'EPMS Customer ID'));

// ============================= START USER FIELDS
echo $this->Form->input('Customer.customer_type', array(		
	'type' => 'select',
	'options' => $customer_type,
	'default' => 'AMP',
	'label' => 'Customer Type'
));
echo $this->Form->input('User.username', array('label' => 'Customer Name'));
echo $this->Form->input('User.id', array('type' => 'hidden'));
echo $this->Form->input('Customer.id', array('type' => 'hidden'));
echo $this->Form->input('User.folder', array('type' => 'hidden'));
echo $this->Form->input('User.role', array('type' => 'hidden'));
echo $this->Form->input('User.parent_id', array('type' => 'hidden'));
echo $this->Form->input('Address.id', array('type' => 'hidden'));
// ============================= END USER FIELDS
// 
// ============================= START ADDRESS FIELDS
echo $this->Form->input('Address.type', array('type' => 'hidden','value' => 'shipping'));
echo $this->Form->input('Address.address');
echo $this->Form->input('Address.address2');
echo $this->Form->input('Address.city');
echo $this->FgForm->stateInput('Address', $stateList);
echo $this->Form->input('Address.zip', array('label' => 'Postal code'));
echo $this->FgForm->countryInput('Address', $countryList);

echo $this->Html->tag('legend', __('Primary Contact'));
echo $this->Form->input('Address.first_name');
echo $this->Form->input('Address.last_name');
echo $this->Form->input('Address.email');
echo $this->Form->input('Address.phone');
// ============================= END ADDRESS FIELDS
// 
//=========================== Rental and Pull Charge Fields
if (isset($this->request->data['Customer']['id'])) {
	$role = $this->Session->read('Auth.User.role');
	if ($role == 'Admins Manager' || $role == 'Staff Manager') {
		echo $this->Html->tag('fieldset'); // Opening fieldset
			$marker = 'Charge-' . $userId;
			echo $this->Html->tag('legend', __('Rental and Pull Charges'), array('id' => $marker, 'class' => 'toggle'));
			echo $this->Html->div($marker . ' hide', NULL);
				echo $this->Form->input('Customer.rent_qty');
				echo $this->Form->input('Customer.rent_unit');
				echo $this->Form->input('Customer.rent_price');
			echo '</div>'; // close toggling div
		echo '</fieldset>'; //close fieldset
	}
}

//=========================== Shipping
echo $this->Html->tag('fieldset'); // Opening fieldset
$marker = 'ShippingAccounts-' . $userId;
echo $this->Html->tag('legend', __('Shipping Accounts'), array('id' => $marker, 'class' => 'toggle'));
echo $this->Html->div($marker . ' hide', NULL);
echo $this->Form->input('Address.fedex_acct', array('label' => 'FedEx Acct'));
echo $this->Form->input('Address.ups_acct', array('label' => 'UPS Acct'));
echo '</div>'; // close toggling div
echo '</fieldset>'; //close fieldset
//=========================== Preferences
echo $this->Html->tag('fieldset'); // Opening fieldset
$marker = 'Prefs-' . $userId;
echo $this->Html->tag('legend', __('Preferences'), array('id' => $marker, 'class' => 'toggle'));
echo $this->Html->div($marker . ' hide', NULL);
echo $this->FgForm->folderCheck('Customer', 'allow_backorder', array('label' => 'Allow customer to backorder items'));
echo $this->FgForm->folderCheck('Customer', 'allow_direct_pay', array('label' => 'Allow customer to pay directly for items'));
echo $this->FgForm->folderCheck('Customer', 'release_hold', array('label' => 'Require staff member to release all orders'));
if (!empty($this->request->data['Customer']['token'])) {
	echo $this->FgHtml->decoratedTag('token:', 'p', $this->request->data['Customer']['token']);
	echo $this->FgForm->button('Update Token', array(
		'type' => 'button',
		'bind' => 'click.updateToken'
	));
}
//=========================== Logo
echo $this->Html->div('imageBlock', null);
echo $this->Html->tag('legend', __('Logo Upload'), array('class' => 'toggle', 'id' => 'logo'));
echo $this->Html->tag('fieldset', null, array('class' => 'logo hide'));
echo $image;
$imageLabel = ($image) ? 'Replace Logo' : 'Choose Logo';
echo $this->Form->input('Logo.img_file', array(
	'type' => 'file',
	'label' => $imageLabel
));
echo '</fieldset>';//close image fieldset

//echo $this->FgForm->folderCheck('Customer', 'taxable', array('label' => 'Check if customer pays sales tax'));
echo '</div>'; // close toggling div
echo '</fieldset>'; //close fieldset

echo '</fieldset>'; //close fieldset
?>
