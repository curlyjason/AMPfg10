<?php
echo $this->Html->div($class, null);

if ($class == 'observerDisplay') {;
    $modelAlias = 'Observer';
    $name = 'observer_name';
    $headers = array('Observer', 'Type', 'Tools');
    $idPrefix = 'observer';
} else {
    $modelAlias = 'UserObserver';
    $name = 'user_name';
    $headers = array('I Observe', 'Type', 'Tools');
    $idPrefix = 'userObserver';
}
if (($modelAlias === 'Observer' && $group === 'Admins') || ($modelAlias === 'UserObserver' && $access === 'Manager')) {
	$tool = true;
} else {
	$tool = false;
}
echo ($tool) ? $this->FgForm->newRequestButton() : '';
echo $this->FgHtml->tag('h3', $heading, array('class' => 'grainDisplay'));

// parse location records into a cake tableCells compatible array
$tableArray = array();
if ($grain[$modelAlias] != array()) {
    foreach ($grain[$modelAlias] as $key => $value) {
        $buttonId = $idPrefix . $value['id'];
        $dButtonAttr = array('id' => 'd' . $buttonId, 'bind' => 'click.observerDelete');
        $eButtonAttr = array('id' => 'e' . $buttonId);
        $rows[] = array(
			array($value[$name],array('class' => 'name')), 
			array($value['type'],array('class' => 'type')), 
			($tool ? $this->FgForm->deleteRequestButton($dButtonAttr) . ' ' . $this->FgForm->editRequestButton($eButtonAttr) : ''));
    }
} else {
    $rows = array();
}

echo $this->FgHtml->tag('Table', null, array('class' => 'order'));
echo $this->Html->tableHeaders($headers);
echo $this->FgHtml->tableCells($rows)
?>
</table>
</div>