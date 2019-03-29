<?php
	//vars
	$itemId = $data['id'];
	$count = count($data['Location']);
	$edit = $this->Html->tag('span', 'Edit', array ('class' => 'locationEditText'));

	//header
	echo $this->Html->div('locations', NULL, array('itemId' => $itemId, 'id' => "ord-$id--item-$itemId"));
	echo $this->Html->tag('h4', "Locations ($count) $edit", array('class' => 'locationsHeader', 'bind' => 'click.editLocations'));
	echo $this->Html->tag('ul', NULL);

	//locations
	if (is_array($data['Location'])) {
		foreach ($data['Location'] as $location) {
		//setup data spans
		$row = ($location['row']) ? ' R-' . $this->Html->tag('span', $location['row'], array('class' => "locNum locRow-{$location['id']}")) : '';
		$bin = ($location['bin']) ? ' B-' . $this->Html->tag('span', $location['bin'], array('class' => "locNum locBin-{$location['id']}")) : '';
		$building = ($location['building']) ? $this->Html->tag('span', $location['building'], array('class' => "locBldg-{$location['id']}")) : '';

		//create an output as an li
		echo $this->Html->tag('li', $building . $row . $bin);
	}
}	
	//close
	echo '</ul></div>';