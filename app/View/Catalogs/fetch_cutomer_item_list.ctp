<?php
if ($itemList) {
	echo $this->Form->input('Catalog.item_id', array(
		'type' => 'select',
		'options' => $itemList,
		'div' => FALSE,
		'empty' => 'Choose an item',

		'label' => FALSE,
		'bind' => 'change.existingItemChange'
	));
} else {
	echo FALSE;
}	
