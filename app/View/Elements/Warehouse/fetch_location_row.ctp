<?php
	$locId = 'ZZZ';
	if(isset($this->request->data[$index]['Location']['item_id'])){
		$itemId = $this->request->data[$index]['Location']['item_id'];
		$locId = $this->request->data[$index]['Location']['id'];
	}
	$row[] = array(
		$this->FgForm->input("$index.Location.id", array('type' => 'hidden')) .
		$this->FgForm->input("$index.Location.item_id", array('value' => $itemId, 'type' => 'hidden')),
		$this->FgForm->input("$index.Location.building", array('label' => false, 'div' => false, 'class' => 'building', 'options' => $buildings)),
		$this->FgForm->input("$index.Location.row", array('div' => false, 'label' => false, 'class' => 'row', 'options' =>array_combine(range(1,$rowMax,1),range(1,$rowMax,1)))),
		$this->FgForm->input("$index.Location.bin", array('div' => false, 'label' => false, 'class' => 'bin', 'options' =>array_combine(range(1,$binMax,1),range(1,$binMax,1)))),
		$this->Html->tag('span', /* $this->Html->image('icon-remove') */ '', array('class' => 'remove', 'location_id' => $locId))
	);
echo $this->FgHtml->tableCells($row);
?>