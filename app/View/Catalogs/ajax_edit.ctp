<?php
//=========================
// Catalogs/ajax_edit.ctp
//=========================

// <editor-fold defaultstate="collapsed" desc="Variables">
$optionsActive = array(
	'options' => array(
		0 => 'Inactive',
		1 => 'Active'
	)
);

$optionsYes = array(
	'options' => array(
		0 => 'No',
		1 => 'Yes'
	)
);

$radioAttrTrue = array(
	'type' => 'radio',
	'legend' => false,
	'value' => 1,
	'empty' => false
);

$radioAttrFalse = array(
	'type' => 'radio',
	'legend' => false,
	'value' => 0,
	'empty' => false
);

$folderWith = false;
$folderWithout = false;
$folder = false; 
preg_match('/[a-z]*_/', $this->request->params['action'], $action);

//set folder variable if not adding
if ($action[0] != 'add_') {
	$folder = ($this->request->data['Catalog']['folder']);
}

//setup image
if (isset($this->request->data['Item']['Image'][0]['img_file'])) {
    $image = $this->FgHtml->image('image' . DS . 'img_file' . DS . $this->request->data['Item']['Image'][0]['id'] . DS . 'x160y120_' . $this->request->data['Item']['Image'][0]['img_file'], array('id' => 'ajaxEditImage'));
} else {
    $image = $this->FgHtml->image('image' . DS . 'img_file' . DS . 'no' . DS . 'x160y120_' . 'image.jpg', array('id' => 'ajaxEditImage'));
};

//base warning variables
$warning = '';

$catalogTypeOptions = array(
		1 => 'Kit',
		2 => 'Folder',
		4 => 'Product'
	);

//shows and hides folder and kit selectors based upon add or edit action
if ($action[0] == 'add_') {
	$radioType = 'radio';
	$inputType = 'input';
} else {
	$radioType = 'hidden';
	$inputType = 'hidden';
}

// </editor-fold>

//============================================================
// OPENING DIV TO MAKE COMPATIBLE WITH USER ADD FORM FILTERING
//============================================================

echo $this->FgHtml->div('ajaxEditPull', NULL);

	echo $image;
	//============================================================
	// CATALOG INPUTS, ALWAYS AVAILABLE
	//============================================================

	echo $this->FgHtml->tag('legend', __('Add Catalog Item'), array(
		'id' => 'treeFormLegend'
	));

	//============================================================
	// MULTIPLE CATALOG CONNECTION WARNING - WITH NAME
	//============================================================
	if (isset($this->request->data['Item']['Catalog'])) {
		$warning = $this->FgHtml->countAlert(count($this->request->data['Item']['Catalog']));
	}
	if (!empty($this->request->data['Catalog']['id'])) {
		$cat_id = $this->request->data['Catalog']['id'];
	} else {
		$cat_id = '';
	}
	echo $this->Form->input('Catalog.name', array(
			'label' => 'id' . $cat_id . ' ' . $warning .' Name'
		));
	//setup for catalog type input
	if ($action[0] == 'add_' && !isset($this->request->data['Catalog']['type'])) {
		$typeType = 'radio';
	} else {
		$typeType = 'hidden';
	}

	//catalog type (Kit, Folder, Product)
	echo $this->FgForm->input('Catalog.type', array(
		'type' => $typeType,
		'options' => $catalogTypeOptions,
		'legend' => FALSE,
		'empty' => FALSE,
		'default' => PRODUCT,
		'class' => 'catalogType',
		'bind' => 'change.catalogTypeRadio'
	));

	echo $this->Form->input('Catalog.active', array_merge($optionsActive, $radioAttrTrue));

	//============================================================
	// CATALOG INPUTS, NON-Folder ONLY
	//============================================================

	if (!$folder) {
		echo $this->FgHtml->div('nonFolder', null);
		
		// <editor-fold defaultstate="collapsed" desc="Kit Preference section">
			//Kit Preferences
		$kitType = (isset($this->request->data['Catalog']['type'])) ? $this->request->data['Catalog']['type'] : '';
		$parentType = (isset($this->request->data['ParentCatalog']['type'])) ? $this->request->data['ParentCatalog']['type'] : '';
			if ($kitType & KIT || (($kitType & COMPONENT) && ($parentType &	ORDER_COMPONENT)) || ($action[0] == 'add_' && $kitType & (COMPONENT | ORDER_COMPONENT))) {
				echo $this->FgHtml->div('kitBlock', null);
			} else {
				echo $this->FgHtml->div('kitBlock hide', null);
			}
			echo $this->FgHtml->tag('legend', __('Kit Preferences'), array('class' => 'toggle', 'id' => 'kitFields'));
			echo $this->FgHtml->tag('fieldset', null, array('class' => 'kitFields hide help', 'help' => 'Kit Preferences'));
				$options = array(
					INVENTORY_BOTH => 'Inventory Kits and Components Both',
					INVENTORY_KIT => 'Inventory Kits Only',
					ON_DEMAND => 'Make Kits on Demand Only'
				);
				
				if (($action[0] != 'add_' && ($this->request->data['Catalog']['type'] & KIT)) ||($action[0] == 'add_' &&  (($kitType & COMPONENT) != COMPONENT))) {
					echo $this->FgForm->radio('Catalog.kit_prefs', $options, array(
						'label' => 'Inventory Options',
							'default' => INVENTORY_BOTH
					));
				}
				
				
				echo $this->FgForm->folderCheck('Catalog', 'can_order_components', array(
					'options' => array((COMPONENT | ORDER_COMPONENT) => 'Users Can Order Components'),
					'div' => array(
						'id' => 'CanOrderComponents'
				)));
			echo '</fieldset>'; //close kit fieldset
			echo '</div>'; //close kitBlock div// 
		//</editor-fold>
		
		if (empty($this->request->data['Catalog']['id'])) {
			echo $this->FgHtml->para('advanced instruction', 'Item source for this catalog entry');
				echo $this->FgForm->input('Item.source', array(
					'options' => array('New', 'Existing'),
					'type' => $radioType,
					'value' => 0,
					'legend' => false
				));
				echo $this->Form->input('Catalog.item_id', array(
					'type' => $inputType,
					'items' => $items, 
					'empty' => 'Choose an item', 
					'label' => '',
					'bind' => 'change.existingItemChange'
				));
		}

	//Item Block
		echo $this->FgHtml->div('itemBlock', null);
			echo $this->FgHtml->tag('legend', __('Item'), array('class' => 'toggle', 'id' => 'item'));
			echo $this->FgHtml->tag('fieldset', null, array('class' => 'item'));
				echo $this->Form->input('Item.id', array('type' => 'hidden'));
				echo $this->Form->input('Catalog.id', array('type' => 'hidden'));
				echo $this->Form->input('Catalog.item_code');
				echo $this->Form->input('Catalog.customer_item_code', array('label' => 'Cust Item Code'));
				echo $this->Form->input('Catalog.description');
			echo '</fieldset>';
		echo '</div>';//close itemBlock

	//Inventory State
		if ($action[0] != 'add_') {
			echo $this->FgHtml->div('inventoryStateBlock', null);
				echo $this->FgHtml->tag('legend', __('Inventory State'), array('class' => 'toggle', 'id' => 'inventoryState'));
				echo $this->FgHtml->tag('fieldset', null, array('class' => 'inventoryState hide'));

					$params = array(
						'alias' => 'Catalog',
						'itemLimitBudget' => TRUE,
						'status' => 'Invalid',
						'title' => 'Inventory after fulfilling all orders'
					);

					echo $this->FgHtml->decoratedTag('Total', 'p', $this->Status->qtyContent($this->request->data, $params, 1));
					echo $this->FgHtml->decoratedTag('in units of ', 'p', (round($this->request->data['Catalog']['sell_quantity'], 0) . ' ' . $this->request->data['Catalog']['sell_unit']));
					echo $this->FgHtml->calculatePendingProduct($this->request->data);
					echo $this->element('Warehouse/locations', array('data' => $this->request->data['Item'], 'id' => $this->request->data['Item']['id']));
				echo '</fieldset>';//close inventoryState
			echo '</div>';//close inventoryStateBlock
		}

	//Pricing & Units
		echo $this->FgHtml->div('pricingBlock', null);
			echo $this->FgHtml->tag('legend', __('Pricing & Units'), array('class' => 'toggle', 'id' => 'pricingUnits'));
			echo $this->FgHtml->tag('fieldset', null, array('class' => 'pricingUnits hide'));
					echo $this->FgForm->input('Catalog.sell_unit', array(
						'default' => 'ea',
						'label' => 'Sell Unit',
						'bind' => 'change.sellUnitControl'
					));
					echo $this->FgForm->input('Catalog.sell_quantity', array(
						'label' => 'Qty/Sell Unit',
						'default' => 1,
						'bind' => 'change.sellQtyControl'
					));
					echo $this->FgForm->input('Catalog.price', array(
						'default' => '0.00',
						'label' => 'Price/Unit'
					));
					echo $this->FgForm->input('Item.po_unit', array(
						'default' => 'ea',
						'label' => 'Purchase Unit',
					));
					echo $this->FgForm->input('Item.po_quantity', array(
						'label' => 'Qty/Purch Unit',
						'default' => 1
					));
					echo $this->FgForm->input('Item.cost', array(
						'default' => '0.00',
						'label' => 'Cost/Purch Unit'
					));

					echo $this->FgForm->input('Item.vendor_id', array(
						'type' => 'select',
						'options' => $ItemVendorId,
						'empty' => 'Choose a vendor',
						'label' => 'Vendor',
						'default' => 5
					));
					echo $this->FgForm->input('Item.po_item_code', array('label' => 'PO item code'));

					echo $this->FgHtml->image('transparent.png');

//				echo '</div>'; //close pricing
			echo '</fieldset>';//close pricingUnits
		echo '</div>';//close pricingBlock

	//Inventory Triggers
		echo $this->FgHtml->div('inventoryBlock', null);
			echo $this->FgHtml->tag('legend', __('Inventory Trigger Levels'), array('class' => 'toggle', 'id' => 'inventory'));
			echo $this->FgHtml->tag('fieldset', null, array('class' => 'inventory hide'));
				echo $this->FgForm->input('Item.reorder_level', array(
					'label' => 'Reorder at',
					'default' => 1
				));
				echo $this->FgForm->input('Item.reorder_qty', array(
					'label' => 'Qty/reorder',
					'default' => 0
				));
				if ($action[0] == 'add_') {
					echo $this->FgForm->input('Item.quantity', array(
						'default' => 0,
						'label' => 'Initial Inventory'
					));
				}
				echo $this->FgForm->input('Catalog.max_quantity', array('label' => 'Max order qty'));
			echo '</fieldset>';//close inventory
		echo '</div>';//close inventoryBlock
		
			//Image Upload
		echo $this->FgHtml->div('imageBlock', null);
			echo $this->FgHtml->tag('legend', __('Image Upload'), array('class' => 'toggle', 'id' => 'image'));
			echo $this->FgHtml->tag('fieldset', null, array('class' => 'image hide'));
				echo $image;
				$imageLabel = ($image) ? 'Replace Image' : 'Choose Image';
				echo $this->FgForm->input('Image.img_file', array(
					'type' => 'file',
					'label' => $imageLabel
				));
			echo '</fieldset>';//close image fieldset
		echo '</div>';//close the imageBlock

		echo '</div>'; //closing the nonFolder div
	}
echo '</div>'; //closing the ajaxEditPull div
?>
