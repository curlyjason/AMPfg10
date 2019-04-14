<?php
//Setup basic variables
$userId = $this->Session->read('Auth.User.id');
//setup image

/**
 * @todo Build a helper class for creating these image strings
 * @todo Try to remove this ugly inline css
 */
$style = 'margin-left: 25%';
if (isset($this->request->data['Logo']['img_file'])) {
	$logo = $this->Html->image(
			'logo' . DS . 'img_file' . DS . 
			$this->request->data['Logo']['id'] . DS . 
			'x160y120_' . $this->request->data['Logo']['img_file'], 
			['id' => 'ajaxEditImage', 'style' => $style]
		);
} else {
	$logo = $this->Html->image(
		'image' . DS . 'img_file' . DS . 'no' .
		DS . 'x160y120_' . 'image.jpg', ['id' => 'ajaxEditImage', 'style' => $style]
	);
};


echo $this->Html->tag('fieldset'); // Opening fieldset

echo $this->Html->tag('legend', __('Edit Customer'));

echo $this->Form->input('customer_code', ['label' => 'EPMS Customer ID']);

// ============================= START USER FIELDS
echo $this->Form->input('Customer.customer_type', array(		
	'type' => 'select',
	'options' => $customer_type,
	'default' => 'AMP',
	'label' => 'Customer Type'
));
echo $this->Form->input('User.username', ['label' => 'Customer Name']);
echo $this->Form->input('User.id', ['type' => 'hidden']);
echo $this->Form->input('Customer.id', ['type' => 'hidden']);
echo $this->Form->input('User.folder', ['type' => 'hidden']);
echo $this->Form->input('User.role', ['type' => 'hidden']);
echo $this->Form->input('User.parent_id', ['type' => 'hidden']);
echo $this->Form->input('Address.id', ['type' => 'hidden']);
// ============================= END USER FIELDS
// 
// ============================= START ADDRESS FIELDS
echo $this->Form->input('Address.type', ['type' => 'hidden','value' => 'shipping']);
echo $this->Form->input('Address.address');
echo $this->Form->input('Address.address2');
echo $this->Form->input('Address.city');
echo $this->FgForm->stateInput('Address', $stateList);
echo $this->Form->input('Address.zip', ['label' => 'Postal code']);
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
			echo $this->Html->tag(
					'legend', 
					__('Rental and Pull Charges'), 
					['id' => $marker, 'class' => 'toggle']
				);
			echo $this->Html->div($marker . ' hide', NULL);
				echo $this->Form->input('Customer.rent_qty');
				echo $this->Form->input('Customer.rent_unit');
				echo $this->Form->input('Customer.rent_price');
				echo $this->Form->input('Customer.order_pull_charge', ['label' => 'First Item Charge']);
				echo $this->Form->input('Customer.item_pull_charge', ['label' => 'Additional Item Charge']);
			echo '</div>'; // close toggling div
		echo '</fieldset>'; //close fieldset
	}
}

//=========================== Shipping
echo $this->Html->tag('fieldset'); // Opening fieldset
$marker = 'ShippingAccounts-' . $userId;
echo $this->Html->tag(
		'legend', 
		__('Shipping Accounts'), 
		['id' => $marker, 'class' => 'toggle']
	);
echo $this->Html->div($marker . ' hide', NULL);
echo $this->Form->input('Address.fedex_acct', ['label' => 'FedEx Acct']);
echo $this->Form->input('Address.ups_acct', ['label' => 'UPS Acct']);
echo '</div>'; // close toggling div
echo '</fieldset>'; //close fieldset
//=========================== Preferences
echo $this->Html->tag('fieldset'); // Opening fieldset
$marker = 'Prefs-' . $userId;
echo $this->Html->tag(
		'legend', 
		__('Preferences'), 
		['id' => $marker, 'class' => 'toggle']
	);
echo $this->Html->div($marker . ' hide', NULL);
echo $this->FgForm->folderCheck(
		'Customer', 
		'allow_backorder', 
		['label' => 'Allow customer to backorder items']
	);
echo $this->FgForm->folderCheck(
		'Customer', 
		'allow_direct_pay', 
		['label' => 'Allow customer to pay directly for items']
	);
echo $this->FgForm->folderCheck(
		'Customer', 
		'release_hold', 
		['label' => 'Require staff member to release all orders']
	);
if (!empty($this->request->data['Customer']['token'])) {
	echo $this->FgHtml->decoratedTag(
			'token:', 
			'p', 
			$this->request->data['Customer']['token']
		);
	echo $this->FgForm->button('Update Token', array(
		'type' => 'button',
		'bind' => 'click.updateToken'
	));
}
echo '</div>'; // close toggling div
echo '</fieldset>'; //close fieldset
//=========================== Logo
echo $this->Html->tag('fieldset'); // Opening fieldset
$marker = 'Brand-' . $this->request->data('User.id');
echo $this->Html->tag(
		'legend', 
		__('Branding options'), 
		['class' => 'toggle', 'id' => 'logo']
	);
echo $this->Html->div('imageBlock', null);
echo $this->Html->tag('fieldset', null, ['class' => 'logo hide']);

echo $this->Form->button('Copy Company Address', ['bind' => 'click.fillBrandingAddressValues']);
echo $this->Form->input('Preference.branding.company', ['label' => 'Comapany name']);
echo $this->Form->input('Preference.branding.address1', ['label' => 'Address line 1']);
echo $this->Form->input('Preference.branding.address2', ['label' => 'Address line 2']);
echo $this->Form->input('Preference.branding.address3', ['label' => 'Address line 3']);

echo $this->Form->input(
		'Preference.branding.customer_user_id', 
		['type' => 'hidden', 'value' => $this->request->data('User.id')]);

echo $logo;
$imageLabel = ($logo) ? 'Replace Logo' : 'Choose Logo';
echo $this->Form->input('Logo.img_file', array(
	'type' => 'file',
	'label' => $imageLabel
));
echo '</fieldset>';//close image fieldset

echo '</div>'; // close toggling div
echo '</fieldset>'; //close fieldset

echo '</fieldset>'; //close fieldset
?>
