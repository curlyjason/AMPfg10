<?php
	$this->start('address_shipping');
		if (!empty($data['shipping'])) {
			echo $this->Html->para(null, $data['shipping'][0]);
			echo $this->Html->para(null, $data['shipping'][1]);
			echo ($data['shipping'][2] == '') ? '' : $this->Html->para(null, $data['shipping'][2]);
			echo $this->Html->para(null, $data['shipping'][3]);
		}
	$this->end();
