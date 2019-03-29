<?php
echo $this->FgHtml->div('locationForm', NULL);
$row = array();
//if (!empty($locations)) {
//	foreach ($locations as $index => $record) {
//		$row[] = array(
//			$this->FgForm->input("$index.Location.id", array('value' => $record['Location']['id'], 'type' => 'hidden')) .
//			$this->FgForm->input("$index.Location.item_id", array('value' => $record['Location']['item_id'], 'type' => 'hidden')),
//			$this->FgForm->input("$index.Location.building", array('value' => $record['Location']['building'], 'label' => false, 'div' => false, 'class' => 'building', 'options' => $buildings)),
//			$this->FgForm->input("$index.Location.row", array('value' => $record['Location']['row'], 'div' => false, 'label' => false, 'class' => 'row', 'options' =>array_combine(range(1,$rowMax,1),range(1,$rowMax,1)))),
//			$this->FgForm->input("$index.Location.bin", array('value' => $record['Location']['bin'], 'div' => false, 'label' => false, 'class' => 'bin', 'options' =>array_combine(range(1,$binMax,1),range(1,$binMax,1)))),
//			$this->Html->tag('span', /* $this->Html->image('icon-remove') */ '', array('class' => 'remove', 'location_id' => $record['Location']['id']))
//		);
//	}
//}

echo $this->FgForm->create('Location');
	echo $this->FgHtml->tag('table', null, array('id' => 'locationTable'));
	echo $this->FgHtml->tableHeaders(array('', 'Building', 'Row', 'Bin', 'X'));
if (!empty($locations)) {
	$this->request->data = $locations;
	foreach ($locations as $index => $record) {
		echo $this->element('Warehouse/fetch_location_row', array('buildings' => $buildings, 'rowMax' => $rowMax, 'binMax' => $binMax, 'index' => $index));
	}
}
echo '</table>';
echo $this->FgForm->button('New entry', array('type' => 'button', 'id' => 'newFee', 'bind' => 'click.addNewLocation'));
echo $this->FgForm->button('Submit', array('type' => 'button', 'id' => 'validate', 'bind' => 'click.submitLocations'));
echo $this->FgForm->button('Cancel', array('type' => 'button', 'bind' => 'click.cancelLocations'));
echo $this->FgForm->end();
echo '</div>';
?>
