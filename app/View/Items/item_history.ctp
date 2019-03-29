<?php

//$this->FgHtml->ddd($item, 'item');
echo $this->Html->tag('h1', 'Item Review for ' . $item['Item']['name']);

foreach ($item as $alias => $records) {
	$header = $this->FgHtml->makeHeadersFromStandardArray($records);
	$rows = $this->FgHtml->makeRowsFromStandardArray($records);
	echo $this->Html->tag('h2', $alias, array('class' => 'sectionHeader'));
//	$this->FgHtml->ddd($records);
	echo '<table>';
//	$this->FgHtml->ddd($header, 'header');
	echo $this->Html->tableHeaders($header);
//	$this->FgHtml->ddd($rows, 'rows');
	echo $this->Html->tableCells($rows);
	echo '</table>';
}
echo $this->element('Item/activity');
