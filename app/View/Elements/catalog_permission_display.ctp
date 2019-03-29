<?php
echo $this->FgHtml->div('catalogPermissionDisplay',null,array(
	'id' => $this->FgHtml->secureSelect($grain['User']['id'])
    ));

	echo ($access === 'Manager') ? $this->FgForm->editRequestButton() : '';

    $modelAlias = 'Catalog';
    $name = 'name';
//    $headers = array('I Observe', 'Type', 'Tools');

    echo $this->FgHtml->tag('h3', $heading, array('class' => 'grainDisplay'));

// parse location records into a cake tableCells compatible array
$tableArray = array();

if ($grain[$modelAlias] != array()) {
    foreach ($grain[$modelAlias] as $key => $value) {
        $rows[] = array($value[$name]);
    }
} else {
    $rows = array();
}
echo $this->FgHtml->div('target', '&nbsp;'); //empty div to receive the edit form
echo $this->FgHtml->tag('Table', null, array('class' => 'order'));
echo $this->FgHtml->tableCells($rows)
?>
</table>
</div>