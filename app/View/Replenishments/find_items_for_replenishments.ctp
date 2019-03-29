<?php
$this->FgHtml->div('findSection', NULL);
	$count = $this->request->data['Replenishment']['totalCount'];
//	$this->FgHtml->ddd($findItems, 'foundSetData');
	
	echo $this->FgHtml->tag('h3', 'Found Items');
	// a div to contain the vendor's items
	echo $this->FgHtml->div("foundItems", NULL);

		foreach ($findItems as $index => $item) {
			$dataItems[$item['Item']['id']] = $item;
			$dataItems[$item['Item']['id']]['Item']['index'] = $item['Item']['id'];
			echo $this->FgForm->input("SearchItem.$count.item_id", array(
				'type' => 'checkbox',
				'label' => $item['Item']['name'],
				'value' => $item['Item']['id'],
				'cost' => $item['Item']['cost'],
				'index' => $item['Item']['id'],
				'bind' => 'click.itemChoiceCheckboxes',
				'hiddenField' => FALSE));
			$count++; // count provides an index into the itemData json object on the page
		}
	echo '</div>'; // end of foundItems div
echo '</div>'; // end of findSection div

        echo "<script type=\"text/javascript\">
//<![CDATA[
// global data for javascript\r";
        echo "var tempData = " . json_encode($dataItems) . ";";
		echo "$.extend(formData,tempData);";
        echo"\r//]]>
</script>";


?>