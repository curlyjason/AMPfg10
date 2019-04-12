<?php
$row = [];
$row[] = [
	    [$this->Form->input('Label.name', ['label' => 'Label Name'])
		. $this->Form->input("Label.id", ['type' => 'hidden'])
		. $this->Form->input("Label.order_id", ['type' => 'hidden']), ['colspan' => 3]
    ]];
foreach ($this->request->data['Label']['items'] as $index => $item) {
	$row[] = [
		$this->Form->input("Label.items.$index.include", ['type' => 'checkbox', 'div' => FALSE, 'label' => 'Inclued', 'bind' => 'click.include']),
		$this->Form->input("Label.items.$index.quantity", ['div' => FALSE, 'label' => "Quantity ({$item['quantity']})"]),
		$this->Html->tag('span', $item['name']
			. $this->Form->input("Label.items.$index.name", ['type' => 'hidden']))
	];
}
?>
// output the form containing a table

<?= $this->Form->create('Label', ['url'=>['controller' => $this->request->controller, 'action' => 'saveNewLabel']]);?>
<table id="editLabel">
    <?= $this->Html->tableCells($row, array('class' => 'omit itemRow'), array('class' => 'omit itemRow'));?>
</table>
<?= $this->Form->button('Submit'); ?>
<?= $this->Form->end(); ?>