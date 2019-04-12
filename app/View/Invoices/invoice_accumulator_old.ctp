<!--<link type="text/css" href="cake.generic.css" rel="stylesheet" />-->
<!--<link type="text/css" href="ampfg.css" rel="stylesheet" />-->
<link type="text/css" href="invoice.pdf.css" rel="stylesheet" />
<div class="wrapper">
	<div class="topMatter">
		<div class="type">
			<?php
				echo $this->fetch('type');
			?>
		</div>
		<div id="amp" class="left">
			<?php
			echo $this->Html->para(null, 'AMP Printing + Graphics');
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

		$rows[] = array(
			array('', array('style' => 'font-size: 6pt;')),
			array($data['groupedSummary'][$group]['label'], array('style' => 'font-size: 10pt; font-weight: bold;', 'colspan' => 4)),
			array($this->Number->currency($data['groupedSummary'][$group]['total']), array('style' => 'font-size: 10pt; font-weight: bold;'))
		);
		foreach ($charges as $charge) {
			$rows[] = array(
				array($i++, array('style' => 'font-size: 6pt;')),
				array($charge['description'], array('style' => 'font-size: 10pt;')),
				array($charge['quantity'], array('style' => 'font-size: 10pt;')),
				array($charge['unit'], array('style' => 'font-size: 10pt;')),
				array($this->Number->currency($charge['price']), array('style' => 'font-size: 10pt;')),
				array($this->Number->currency($charge['price'] * $charge['quantity']), array('style' => 'font-size: 10pt;')),
			);
		}		
	}

	echo '<table style="width: 7.5in";">';
	echo $this->Html->tableHeaders(array('#', 'Desc', 'Qty', 'Unit', 'Price', 'Subtotal'), array('style' => 'background-color: #ccc;'), array('style' => 'font-size: 8pt; text-align: left; padding: 1pt;'));
	echo $this->Html->tableCells($rows);
	echo '</table>';
	?>
</div>