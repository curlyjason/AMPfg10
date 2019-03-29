<?php
$this->append('css');
	echo $this->Html->css('shipping_label');
$this->end();
$this->append('script');
	echo $this->Html->script('shippingLabel');
$this->end();

$this->start('sidebar');
// Order number label
echo $this->Html->para('labelOrderNumber', $order['reference']['data'][1]);

// Display shipping address
echo $this->Html->tag('h3', 'Shipping Address', array('class' => 'labelHeading'));
foreach ($order['shipping'] as $addressLine) {
	if ($addressLine != '') {
		echo $this->Html->para('labelAddress', $addressLine);
	}
}

echo '<ul id="labels">';
// 'New' button
echo $this->Html->tag('li', $this->Form->button('Create New Label', array('bind' => 'click.newLabel', 'class' => 'labelNewButton')), array('id' => "li-$orderId"));

// List existing labels
if (!empty($labelList)) {
	foreach ($labelList as $labelId => $name) {
		echo $this->Html->tag('li',$this->Form->button($name, array('bind' => 'click.editLabel')) 
				. $this->Html->image('icon-remove.gif', array('bind' => 'click.removeLabel'))
				. $this->Html->image('print.png', array('class' => 'print', 'bind' => 'click.printLabel')),
				array('id' => "li-$labelId"));
	}
}
//$c = 0;
//while ($c++ < 6) {
//	echo $this->Html->tag('li',$this->Form->button("Lable $c", array('bind' => 'click.editLabel')) . $this->Html->image('icon-remove', array('bind' => 'click.removeLabel')),
//			array('id' => "li-"));
//}
echo '</ul>';

$this->end();
?>

<div id="detailLabel">
	<?php // echo $this->element('edit_shipping_label', array('items' => $order['items'])) ?>
</div>
