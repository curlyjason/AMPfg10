<?php
	echo $this->Html->css('shipping_label');
?>
<div id="label">
	<p style="color: #888; margin: 0;">Warehouse<br />
		6955 Sierra Ct<br />
		Dublin CA 94568</p>
	<p>Ship to:</p>
	<div id="address" style="margin-top: 0.125in;">
		<?php
		foreach ($order['shipping'] as $addressLine) {
			if ($addressLine != '') {
				echo $this->Html->para('', $addressLine, array('style' => 'font-size: 20px; margin: 0 0 0 0.125in;'));
			}
		}
		?>
	</div>
	<div id="items" style="margin-top: 0.125in;">
		<p style="margin: 0; padding: 5px 0 0;">This carton contains:</p>
		<?php
		foreach ($items['Label']['items'] as $item) {
			if ($item['include']) {
				echo $this->Html->para('contains', 
						$this->Html->tag('span', $item['quantity'], array('class' => 'qty', 'style' => 'border: thin solid #aaaaaa; display: inline-block; padding: 5px; width: 10%')) 
						. ' ' 
						. $this->Html->tag('span', $item['name'], array('class' => 'name', 'style' => 'border: thin solid #aaaaaa; display: inline-block; padding: 5px; width: 80%')) ,
						array('style' => 'margin: 0; padding: 5px 0 0;')
					);
			}
		} 
		?>
	</div>
</div>