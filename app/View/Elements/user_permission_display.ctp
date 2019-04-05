<?php

echo $this->Html->div('userPermissionDisplay',null,array(
	'id' => $this->FgHtml->secureSelect($grain['User']['id'])
    ));

echo ($access === 'Manager') ? $this->FgForm->editRequestButton() : '';

$modelAlias = 'UserManaged';

echo $this->FgHtml->tag('h3', $heading, array('class' => 'grainDisplay'));

// parse location records into a cake tableCells compatible array
$tableArray = array();
if ($grain[$modelAlias] != array()) {
    foreach ($grain[$modelAlias] as $key => $value) {
        $rows[] = array($this->FgHtml->discoverName($value));
    }
} else {
    $rows = array();
}
echo $this->Html->div('target', '&nbsp;'); //empty div to receive the edit form
echo $this->FgHtml->tag('Table', null, array('class' => 'order'));
//echo $this->Html->tableHeaders($headers);
echo $this->FgHtml->tableCells($rows);
echo '</table>';
echo '</div>';
?>