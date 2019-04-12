<!--<link type="text/css" href="cake.generic.css" rel="stylesheet" />-->
<!--<link type="text/css" href="ampfg.css" rel="stylesheet" />-->
<link type="text/css" href="invoice.print.css" rel="stylesheet" />
<div style="padding: .375in;">
	<div class="topMatter">
		<div class="type">
			<?php
				echo $this->fetch('type');
			?>
		</div>
		<div id="amp" class="left">
			<?php
			echo $this->Html->para(null, $data['customer_type'] == 'AMP' ? "AMP Printing + Graphics" : "Gold Medal Press");
			echo $this->Html->para(null, '6955 Sierra Court');
			echo $this->Html->para(null, 'Dublin, CA 94568');
			?>
		</div>
		<!-- REFERENCE BLOCK -->
		<div class="reference_block">
			<div class="header">
				<?php $this->Accumulator->columns($data['reference']['labels']); ?>
			</div>
			<div class="content_block">
				<?php $this->Accumulator->columns($data['reference']['data']); ?>
			</div>
		</div>
		<!-- BILLING ADDRESS -->
		<div class="section address left">
			<div class="header">
				<p>Customer address</p>
			</div>
			<div class="content_block">
				<?php echo $this->fetch('address_billing'); ?>
			</div>
		</div>

		<div class="summary <?php echo $type; ?>">
			<div class="header">
				<?php echo $this->fetch('summary_header'); ?>
			</div>
			<div class="content_block">
				<?php echo $this->fetch('summary_data'); ?>
			</div>
		</div>
	</div>

	<?php
	
	$rows = array();
	$i = 1;
	foreach ($data['groupedCharges'] as $group => $charges) {
		$addr = $data['groupedSummary'][$group]['shipment'];
		if ($addr != '') {
			$addrName = ($addr['company'] = '') ? $addr['company'] : $addr['first_name'] . ' ' . $addr['last_name'];
			$addrBlock = "<br />$addrName, {$addr['address']}, {$addr['city']}, {$addr['state']} {$addr['zip']}";
//			$addrBlock .= "<br />Tracking #: {$addr['tracking']}";
		} else {
			$addrBlock = '';
		}
		if ($data['groupedSummary'][$group]['label'] != 'General Charges') {
			$ordDate = date('m-d-Y', strtotime($addr['order_date']));
			$shipDate = date('m-d-Y', strtotime($addr['shipment_date']));
			$ordBlock = "<br />Ordered on: $ordDate -- Shipped on: $shipDate";
			$refBlock = "Ref: {$data['groupedSummary'][$group]['reference']}";
			$refBlock .= "<br />Tracking #: {$addr['tracking']}";
		} else {
			$ordBlock = '';
			$refBlock = '';
		}
		$rows[] = array(
			array('', array('style' => 'font-size: 6pt;')),
			array("<span style='font-weight: bold; font-size: 10pt;'>" . $data['groupedSummary'][$group]['label'] ."</span>". $ordBlock . $addrBlock, array('style' => 'font-size: 8pt; font-weight: normal;', 'colspan' => 2)),
			array("<span style='font-size: 10pt;'>" . $refBlock ."</span>", array('style' => 'font-size: 8pt; font-weight: normal;', 'colspan' => 3)),
		);
        if($group != 'general'){
            $this->assign('itemTable', '');
            $this->start('itemTable');
                echo "<p style='font-weight: bold; font-size: 10pt;'>Ordered Items</p>";
                echo $this->Html->tag('table', NULL, array('class' => 'ordItems'));
                echo $this->Html->tableHeaders(array('Qty', 'Item', 'Price', 'SubTotal'));
                echo $this->Html->tableCells($this->Invoice->ordItems($ordItems[$group]));
                echo '</table>';;
            $this->end();
            $rows[] = array(
                array('', array('style' => 'font-size: 6pt;')),
                array($this->fetch('itemTable'), array('style' => 'font-size: 8pt; font-weight: normal;', 'colspan' => 6))
            );
        }

		foreach ($charges as $charge) {
			if ($charge['price'] != 0) {
				$rows[] = array(
				array('', array('style' => 'font-size: 6pt;')),
				array($charge['description'], array('style' => 'font-size: 10pt;')),
				array($charge['quantity'], array('style' => 'font-size: 10pt;')),
				array($charge['unit'], array('style' => 'font-size: 10pt;')),
				array($this->Number->currency($charge['price']), array('style' => 'font-size: 10pt; text-align: right; padding-right: 2px;')),
				array($this->Number->currency($charge['price'] * $charge['quantity']), array('style' => 'font-size: 10pt; text-align: right; padding-right: 2px;')),
			);
			}			
		}
		$rows[] = array(
			array('', array('style' => 'font-size: 6pt;')),
			array('', array('style' => 'font-size: 8pt; font-weight: normal;', 'colspan' => 3)),
			array('Total:', array('style' => 'font-size: 8pt; font-weight: bold; text-align: right; padding-right: 2px;')),
			array($this->Number->currency($data['groupedSummary'][$group]['total']), array('style' => 'font-size: 10pt; font-weight: bold; text-align: right; padding-right: 2px;'))
		);
		$rows[] = array(
			array(' ', array('style' => 'background: black; height: 3px;', 'colspan' => 6))
		);
	}

	echo '<table style="width: 7.5in";", class="invoiceLines">';
	echo $this->FgHtml->tableHeaders(array('#', 'Desc', 'Qty', 'Unit', 'Price', 'Subtotal'), array('style' => 'background-color: #ccc;'), array('style' => 'font-size: 8pt; text-align: left; padding: 1pt;'));
	echo $this->Html->tableCells($rows);
	echo '</table>';
	?>
</div>