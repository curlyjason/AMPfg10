<!--<link type="text/css" href="cake.generic.css" rel="stylesheet" />
<link type="text/css" href="ampfg.css" rel="stylesheet" />-->
<!--<link type="text/css" href="report.css" rel="stylesheet" />-->
<link type="text/css" href="print.inventory_activity.css" rel="stylesheet" />
<h2>
<?php
	echo 'Inventory Activity Report<br />';
	echo $customerName . '<br />';
	echo date('M d, Y', $report['firstTime']) . ' - ' . date('M d, Y', $report['finalTime']) ;
?>
</h2>
<?php
$this->Report->rows['active'] = array();
$this->Report->rows['inactive'] = array();
$this->Report->userGroup = $this->Report->setUserGroupProperty();
if (!empty($report['items'])) {
	foreach ($report['items'] as $item) {


		if ($item['id'] === NULL) {
			continue;
		}
		$this->Report->item = $item;
		
		// handle missing snapshots ===================================
		// item did not exist until after start date
		// or item did not exist during range at all
		if (!isset($item['Snapshot']) || $item['Snapshot'] == NULL) {
			
			$this->Report->item['Snapshot'] = $item['Snapshot'] = array(array(
				'date' => date('M d, Y', $report['finalTime']), 
				'inventory' => 'Item did not exist during report period', 
				'state' => 'notCreated',
				'Clients_code' => $item['customer_item_code'],
				'Staff_code' => $item['item_code']));
		}
		
		if (count($item['Snapshot']) < 2) {
			$this->Report->item['Snapshot'][1] = $item['Snapshot'][1] = $item['Snapshot'][0];
			$this->Report->item['Snapshot'][0]['date'] = $item['Snapshot'][0]['date'] = date('M d, Y', $report['firstTime']);
			$this->Report->item['Snapshot'][0]['inventory'] = $item['Snapshot'][0]['inventory'] = 'Item created after report date';
		}
		// end handle missing snapshots ===================================
			
		$this->Report->active = $item['Snapshot'][0]['state'];

		$this->Report->reportItemActivity();
		
		$activeSection = array(
			array(
				array('Active Items', array('class' => 'section', 'colspan' => 5))
			)
		);
		$inactiveSection = array(
			array(
				array('Inactive Items', array('class' => 'section', 'colspan' => 5))
			)
		);
		$notSection = array(
			array(
				array('Items that didn\'t exist during the reporting period', array('class' => 'section', 'colspan' => 5))
			)
		);
}
?>
	<table id='inventory'>
		<tbody>
	<?php
	if (isset($this->Report->rows['active'])) {
		echo $this->FgHtml->tableCells($activeSection);
		echo $this->FgHtml->tableCells($this->Report->rows['active']);
	}
	
	if (isset($this->Report->rows['inactive'])) {
		echo $this->FgHtml->tableCells($inactiveSection);
		echo $this->FgHtml->tableCells($this->Report->rows['inactive']);
	}
	
	if (isset($this->Report->rows['notCreated'])) {
		echo $this->FgHtml->tableCells($notSection);
		echo $this->FgHtml->tableCells($this->Report->rows['notCreated']);
	}
	?>
		</tbody>
	</table>
<?php
	}
?>