<?php 
	$this->extend('print_accumulator');
	$this->start('type');
		echo $this->Html->tag('h1', ucfirst($type));
	$this->end();

	$this->start('address_billing');
		echo $this->Html->para(null, $data['billing'][0]);
		echo $this->Html->para(null, $data['billing'][1]);
		echo ($data['billing'][2] == '') ? '' : $this->Html->para(null, $data['billing'][2]);
		echo $this->Html->para(null, $data['billing'][3]);
	$this->end();

	$this->start('address_shipping');
		if (!empty($data['shipping'])) {
			echo $this->Html->para(null, $data['shipping'][0]);
			echo $this->Html->para(null, $data['shipping'][1]);
			echo $this->Html->para(null, $data['shipping'][2]);
			echo ($data['shipping'][3] == '') ? '' : $this->Html->para(null, $data['shipping'][3]);
			echo $this->Html->para(null, $data['shipping'][4]);
		}
	$this->end();
	
	$this->start('accumulator_row');
		$this->Accumulator->columns($headerRow);
	$this->end();

	$this->start('line_items');
	foreach ($data['items'] as $item) {
		echo $this->Html->div('line', null);
		$this->Accumulator->columns($item);
		echo '</div>';
	}	
	$this->end();

	$this->start('summary_header');
		echo $this->Accumulator->columns($data['summary']['labels']);
	$this->end();
	
	$this->start('summary_data');
		echo $this->Accumulator->columns($data['summary']['data']);
	$this->end();
?>