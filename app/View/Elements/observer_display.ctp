<div class="<?= $class ?>">

<?php
if ($class == 'observerDisplay') {;
    $modelAlias = 'Observer';
    $name = 'observer_name';
    $headers = ['Observer', 'Type', 'Tools'];
    $idPrefix = 'observer';
} else {
    $modelAlias = 'UserObserver';
    $name = 'user_name';
    $headers = ['I Observe', 'Type', 'Tools'];
    $idPrefix = 'userObserver';
}
if 
		(($modelAlias === 'Observer' && $group === 'Admins') 
		|| ($modelAlias === 'UserObserver' && $access === 'Manager')) 
{
	$tool = true;
} else {
	$tool = false;
}
echo ($tool) ? $this->FgForm->newRequestButton() : '';
echo $this->Html->tag('h3', $heading, ['class' => 'grainDisplay']);

// parse location records into a cake tableCells compatible array
$tableArray = [];
if ($grain[$modelAlias] != []) {
    foreach ($grain[$modelAlias] as $key => $value) {
        $buttonId = $idPrefix . $value['id'];
        $dButtonAttr = ['id' => 'd' . $buttonId, 'bind' => 'click.observerDelete'];
        $eButtonAttr = ['id' => 'e' . $buttonId];
        $rows[] = [
			[$value[$name],['class' => 'name']], 
			[$value['type'],['class' => 'type']], 
			($tool 
				? $this->FgForm->deleteRequestButton($dButtonAttr) 
				. ' ' . $this->FgForm->editRequestButton($eButtonAttr) 
				: ''
			)];
    }
} else {
    $rows = [];
}
?>

<table class="order">
	<?php
	echo $this->Html->tableHeaders($headers);
	echo $this->Html->tableCells($rows)
	?>
</table>
</div>