<?php

//'lowStock', 'itemData', 'otherVendors'
//$this->FgHtml->ddd($lowStock, 'lowStock');
//$this->FgHtml->ddd($itemData, 'itemData');
//$this->FgHtml->ddd($otherVendors, 'otherVendors');
$this->start('css');
echo $this->FgHtml->css('ampfg_grain');
echo $this->FgHtml->css('ampfg_forms');
echo $this->FgHtml->css('replenishment');
$this->end();

$this->start('script');
echo $this->FgHtml->script('replenishment');
echo $this->FgHtml->script('form');
$this->end();

$this->start('jsGlobalVars');
echo 'var formData = ' . json_encode($itemData) . ';';
$this->end();

echo $this->FgForm->create();
echo $this->FgForm->input('po_item_code', array('type' => 'hidden'));

//$this->FgHtml->ddd($itemData['vendorAccess'], 'vendorAccess');

echo $this->Html->div('sidebar', NULL);
	$this->FgForm->input('po_item_code', array('type' => 'hidden'));
	echo $this->Html->tag('fieldset', NULL, array('id' => 'replenishmentTools'));
		echo $this->Html->tag('h2', 'Create Replenishment');
		
		echo $this->FgForm->input('Replenishment.status', array(
			'options' => array(
				'Open' => 'Open',
				'Placed' => 'Placed'
			),
			'type' => 'radio',
			'legend' => FALSE,
			'required' => false,
			'default' => 'Open',
			'before' => 'Set Status: '
//			'after' => '<br/>(Open allows changes, <br/>Placed goes to the warehouse.)'
		));
		echo $this->FgForm->input('Replenishment.fudge', array(
			'options' => array(
				'1' => 'x1',
				'100' => 'x2',
				'1000' => 'x10',
				'10000' => 'x100'
			),
			'type' => 'select',
			'empty' => 'Choose a Multiplier',
			'legend' => false,
			'label' => FALSE,
			'before' => 'Expand range: ',
			'after' => $this->Html->tag('span', ' (reloads page)', array('class' => 'hint')),
			'bind' => 'change.expandCreateReplenishmentScope'
		));
		
		echo '<p>'.$this->Html->link('New Vendor', array(
			'controller' => 'addresses', 
			'action' => 'manageVendors'))
			. $this->Html->tag('span', ' (reloads page)', array('class' => 'hint')).'</p>';

		echo $this->Html->div('search', NULL);
			echo $this->FgForm->input('search', array(
				'bind' => 'change.findItemsForReplenishments'
			));
			echo $this->FgForm->button('Search', array(
				'class' => 'searchButton',
				'bind' => 'click.findItemsForReplenishments',
				'type' => 'button'
			));
		echo '</div>';

	echo '</fieldset>';

	$count = 0;

	echo $this->Html->tag('fieldset', NULL, array('id' => 'vendorSection'));
	echo $this->Html->tag('h2', 'Vendors & Items');
	echo $this->Html->div(NULL, '', array('id' => 'findResult'));
	// Output each vendor (as a radio button)
	// and the items from that vendor (as checkboxes)
	foreach ($lowStock as $vendor => $items) {

		// wrap the vendor block for easy dom traversal
		echo $this->Html->div('vendorSection', NULL);
		$vndr = preg_replace('/[^a-zA-Z0-9]*/', '', $vendor);

		// the vendor
		echo $this->FgForm->input("Replenishment.vendor_id", array('type' => 'radio', 'options' => array($items[0]['Vendor']['id'] => "PO for $vendor"), 'value' => $vendor));

		// a reveal tool for the vendor's items
		echo $this->Html->para('reveal', $this->Html->tag('span', 'Reveal', array(
					'class' => 'toggle', 'id' => "ItemGroup$vndr"
				))
				. ' these ' . count($items) . ' items.');

		// a mass-select tool for this vendor's items
		echo $this->FgForm->input("ItemGroup$vndr", array(
			'selectAll' => "ItemGroup$vndr",
			'type' => 'checkbox',
			'label' => 'Select all in this group',
			'hiddenField' => FALSE,
			'div' => array('class' => "ItemGroup$vndr hide selectAll")
		));

		// a div to contain the vendor's items
		echo $this->Html->div("ItemGroup$vndr hide items", NULL);

			foreach ($items as $index => $item) {
				echo $this->FgForm->input("ReplenishmentItemCk.$count.item_id", array(
					'type' => 'checkbox',
					'label' => $item['Item']['name'],
					'value' => $item['Item']['id'],
					'cost' => $item['Item']['cost'],
					'index' => $item['Item']['id'],
					'bind' => 'click.itemChoiceCheckboxes',
					'hiddenField' => FALSE));
				$count++; // count provides an index into the itemData json object on the page
			}
		echo '</div>'; // end of ItemGroupxx div
	echo '</div>'; // end of vendorSection div
	}
	echo $this->FgForm->input('totalCount', array('type' => 'hidden', 'value' => $count));
	echo $this->Html->div('otherVendors', null);
		echo $this->Html->para('', 'Other possible vendors');
		foreach ($otherVendors as $vendor) {
			echo $this->FgForm->input("Replenishment.vendor_id", array('type' => 'radio', 'options' => array($vendor['Vendor']['id'] => "PO for {$vendor['Vendor']['name']}"), 'value' => $vendor['Vendor']['id']));
		}
	echo '</div>'; // end of other vendors div
	echo '</fieldset>';
echo '</div>'; // end of sidebar

echo $this->Html->div('view', NULL);
		echo $this->Html->div('vendorAddress', NULL);
			echo $this->Html->tag('h2', 'Vendor Address');
			echo $this->Html->div('address', NULL);
			echo $this->FgHtml->decoratedTag('Company', 'p', 'On Choice', array('class' => 'decoration Company'));
			echo $this->FgHtml->decoratedTag('Address', 'p', ' ', array('class' => 'decoration Address'));
			echo $this->FgHtml->decoratedTag('City, State ZIP', 'p', ' ', array('class' => 'decoration Csz'));
			echo $this->Form->input('Replenishment.vendor_company', array('class' => 'vendor', 'type' => 'hidden', 'field_name' => 'name'));
			echo $this->Form->input('Replenishment.vendor_address', array('class' => 'vendor', 'type' => 'hidden', 'field_name' => 'address'));
			echo $this->Form->input('Replenishment.vendor_address2', array('class' => 'vendor', 'type' => 'hidden', 'field_name' => 'address2'));
			echo $this->Form->input('Replenishment.vendor_city', array('class' => 'vendor', 'type' => 'hidden', 'field_name' => 'city'));
			echo $this->Form->input('Replenishment.vendor_state', array('class' => 'vendor', 'type' => 'hidden', 'field_name' => 'state'));
			echo $this->Form->input('Replenishment.vendor_zip', array('class' => 'vendor', 'type' => 'hidden', 'field_name' => 'zip'));
			echo $this->Form->input('Replenishment.vendor_country', array('class' => 'vendor', 'type' => 'hidden', 'field_name' => 'country'));
		echo '</div>'; //close vendorAddress div
	echo '</div>'; //close address div
	echo $this->Html->div('replenishmentClosingButtons');
		echo $this->FgForm->button('Cancel', array(
			'type' => 'button',
			'bind' => 'click.basicCancelButton',
			'class' => 'replenishmentCancel'));

		echo $this->FgForm->button('Save', array(
			'type' => 'submit',
			'bind' => 'click.submitReplenishment',
			'class' => 'btn replenishmentSubmit'));

	echo '</div>';//close replenishmentClosingButtons

	echo $this->Html->tag('h2', 'Replenishment Items');
echo '</div>'; //close
echo $this->FgForm->end();
?>