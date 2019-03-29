<?php
$row = array();

// make the name and id inputs.
$row[] = array(
	array($this->Form->input('Label.name', array('label' => 'Label Name'))
		. $this->Form->input("Label.id", array('type' => 'hidden'))
		. $this->Form->input("Label.order_id", array('type' => 'hidden')), array('colspan' => 3)
));

foreach ($this->request->data['Label']['items'] as $index => $item) {
	
	// make a row for each item
	$row[] = array(
		$this->Form->input("Label.items.$index.include", array('type' => 'checkbox', 'div' => FALSE, 'label' => 'Inclued', 'bind' => 'click.include')),
		$this->Form->input("Label.items.$index.quantity", array('div' => FALSE, 'label' => "Quantity ({$item['quantity']})")),
		$this->Html->tag('span', $item['name']
			. $this->Form->input("Label.items.$index.name", array('type' => 'hidden')))
	);
}

// output the form containing a table

echo $this->Form->create('Label', array('action' => 'saveNewLabel'));
echo '<table id="editLabel">';
echo $this->Html->tableCells($row, array('class' => 'omit itemRow'), array('class' => 'omit itemRow'));
echo '</table>';
//echo $this->Form->button('Submit', array('bind' => 'click.submitLabel'));
echo $this->Form->button('Submit');
echo $this->Form->end();