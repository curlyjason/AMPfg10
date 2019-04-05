<?php
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

unset($itemData['vendorAccess']);
echo $this->FgForm->create();
echo $this->FgForm->input('po_item_code', array('type' => 'hidden'));


echo $this->Html->div('sidebar', NULL);
	$this->FgForm->input('po_item_code', array('type' => 'hidden'));
	echo $this->Html->tag('fieldset', NULL, array('id' => 'replenishmentTools'));
		echo $this->Html->tag('h2', 'Create Replenishment');
		echo $this->Form->input('Replenishment.status', array(
			'type' => 'hidden',
			'value' => 'Open'
		));
		echo $this->Form->input('Replenishment.vendor_id', array(
			'type' => 'select',
			'options' => $vendors,
			'label' => 'Customer',
			'bind' => 'change.selectVendor',
			'empty' => 'Choose a Customer'
		));
	echo '</fieldset>';

	$count = 0;
	$name = isset($this->request->params['pass'][0]) ? 'for<br />' . $vendors[$this->request->params['pass'][0]] : '';
	echo $this->Html->tag('h2', "Items $name");
	echo $this->Html->div(NULL, NULL, array('id' => 'findResult'));
		// Output each vendor (as a radio button)
		// and the items from that vendor (as checkboxes)
		foreach ($itemData as $key => $item) {
			echo $this->Form->input("$key.Item.name", array(
				'type' => 'checkbox',
				'value' => $item['Item']['id'],
				'label' => $item['Item']['name'] . " ({$item['Item']['available_qty']})",
				'bind' => 'click.itemChoiceCheckboxes',
				'index' => $item['Item']['id'],
				'hiddenField' => FALSE
			));
			}
	echo '</div>'; // end of findResult div
echo '</div>'; // end of sidebar

echo $this->Html->div('view', NULL);
	echo $this->Html->div('replenishmentClosingButtons', NULL);
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