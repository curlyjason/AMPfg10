<?php
$this->start('address_shipping');
		if (!empty($data['shipping'])) {
			echo $this->Html->para(null, $data['shipping'][0]);
			echo $this->Html->para(null, $data['shipping'][1]);
			echo $this->Html->para(null, $data['shipping'][2]);
			echo ($data['shipping'][3] == '') ? '' : $this->Html->para(null, $data['shipping'][3]);
			echo $this->Html->para(null, $data['shipping'][4]);
		}
	$this->end();
