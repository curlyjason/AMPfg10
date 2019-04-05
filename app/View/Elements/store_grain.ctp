<?php
	$query = (isset($query)) ? $query : ''; // used to highlight search results
	
	// WRAPPER DIV =========================================
	echo $this->Html->div('item', null);

		// IMAGE
		if (isset($entry['Item']['Image'][0]['id'])) {
			echo $this->Html->image('image' . DS . 'img_file' . DS . $entry['Item']['Image'][0]['id'] . DS . 'x160y120_' . $entry['Item']['Image'][0]['img_file']);
		}

//		// THE GRAIN CONTAINS A FORM
//		echo $this->Form->create(NULL, array(
//			'url' => array('controller' => 'shop', 'action' => 'add'),
//			'class' => 'addToCartForm'));
//
//		// HIDDEN ID INPUT
//		echo $this->Form->input('id', array('type' => 'hidden', 'value' => $entry['Item']['id']));
//
//		// ITEM-LIMIT BUDGET ALERT	
//		$entry['sell_unit'] = $entry['Catalog']['sell_unit'];
//		echo $this->FgHtml->itemLimitAlert($entry, $itemLimitBudget);

		// BUTTON AND END OF FORM
		echo $this->Store->addToCartBlock($entry, $backorderAllow, $itemLimitBudget);
//	echo $this->Form->end();

		echo '<div class="description">';
			echo $this->Html->tag(
					'h4', 
					$this->Html->link(
							$this->Text->highlight($entry['Catalog']['name'], $query), 
							array('controller' => 'catalogs', 'action' => 'view', $entry['Catalog']['id']), 
							array('escape' => FALSE)
					)
			);	
			echo $this->Store->revealComponentsLink($entry);
//            echo $this->FgHtml->markdown($this->Text->highlight($entry['Item']['description'] . '<br />' . $entry['Item']['description_2'], $query));
//            echo $this->FgHtml->
            echo '<ul>';
            echo $this->Html->tag('li','Item Code: ' . $entry['Catalog']['item_code']);
            echo $this->Html->tag('li','Customer Item Code: ' . $entry['Catalog']['customer_item_code']);
            echo '</ul>';
            echo $this->FgHtml->markdown($this->Text->highlight($entry['Catalog']['description'], $query));
		echo '</div>';

	echo '</div>';
	// WRAPPER DIV COMPLETE =======================================
	
	// SHOW KIT COMPONENTS IF THIS IS A KIT =======================================
	if ($entry['Catalog']['type'] & KIT) {
		// This entry is a KIT, so we'll output the components
		// all collapsed. The 'Expose' trigger is with the Kit name
		echo $this->Html->div('hide revealComponents'.$entry['Catalog']['id'], NULL);
			foreach ($entry['Catalog']['Components'] as $index => $component) {
				echo $this->element('store_grain', array('entry' => $component));
			}
		echo '</div>';
	}

?>