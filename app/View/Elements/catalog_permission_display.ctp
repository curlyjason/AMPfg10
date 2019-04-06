<?php
echo $this->Html->div('catalogPermissionDisplay',null,array(
	'id' => $this->FgHtml->secureSelect($grain['User']['id'])
    ));

	echo ($access === 'Manager') ? $this->FgForm->editRequestButton() : '';

    $modelAlias = 'Catalog';
    $name = 'name';
//    $headers = array('I Observe', 'Type', 'Tools');

    echo $this->Html->tag('h3', $heading, array('class' => 'grainDisplay'));

// parse location records into a cake tableCells compatible array
$tableArray = array();

if ($grain[$modelAlias] != array()) {
    foreach ($grain[$modelAlias] as $key => $value) {
        $rows[] = array($value[$name]);
    }
} else {
    $rows = array();
}
echo $this->Html->div('target', '&nbsp;'); //empty div to receive the edit form
echo $this->Html->tag('Table', null, array('class' => 'order'));
echo $this->Html->tableCells($rows)
?>
</table>
</div>