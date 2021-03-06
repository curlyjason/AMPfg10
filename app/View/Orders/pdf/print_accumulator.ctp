<link type="text/css" href="accumulator.print.css" rel="stylesheet" />
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
			<p><?php echo $type == 'order' ? 'Billing' : 'Vendor'; ?> address</p>
		</div>
		<div class="content_block">
			<?php echo $this->fetch('address_billing'); ?>
		</div>
	</div>
	<!-- SHIPPING ADDRESS -->
	<div class="section address right <?php echo $type; ?>">
		<div class="header">
			<p>Shipping Address</p>
		</div>
		<div class="content_block">
			<?php echo $this->fetch('address_shipping'); ?>
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
	<!-- PAGE 1 LINE ITEM SECTION -->

		<?php
		$rows = array();
				foreach ($data['items'] as $item) {
					$rows[] = array(
						array($item['#'], array('style' => 'font-size: 6pt;')),
						array($item['quantity'], array('style' => 'font-size: 10pt;')),
						array($item['code'], array('style' => 'font-size: 10pt;')),
						array($item['name'], array('style' => 'font-size: 10pt;')),
					);
				}
		echo '<table style="width: 7.5in";">';
		echo $this->Html->tableHeaders(array('line', 'Qty', 'Item', 'Name'), array('style' => 'background-color: #ccc;'), array('style' => 'font-size: 8pt; text-align: left; padding: 1pt;'));
		echo $this->Html->tableCells($rows);
		echo '</table>';
		echo '<table class="footerNote">';
		$note = preg_replace("/\n/", "<\p><p class='note' style='font-size: 10pt; font-weight: bold; text-align: left;'>", $data['note']);
		$tableNote = $this->Html->para('note', $note, array('style' => 'font-size: 10pt; font-weight: bold; text-align: left;'));
		echo $this->Html->tableHeaders(array('Note'), array('style' => 'background-color: #ccc;'), array('style' => 'font-size: 8pt; text-align: left; padding: 1pt;'));
		echo $this->Html->tableCells(array('note' => $tableNote));
		echo '</table>';
		?>
	</div>
