<?php
echo $this->Html->div('locationForm', NULL);
$row = array();

echo $this->FgForm->create('Location');
	echo $this->Html->tag('table', null, array('id' => 'locationTable'));
	echo $this->Html->tableHeaders(array('', 'Building', 'Row', 'Bin', 'X'));
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
