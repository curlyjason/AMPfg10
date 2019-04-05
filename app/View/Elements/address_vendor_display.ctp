<?php
echo $this->Html->div('vendorDisplay', null);

    $modelAlias = 'Address';
    $name = 'name';
    $headers = array('ID', 'Name', 'Location', 'Tools');
    $idPrefix = 'vendor';
    
//echo $this->FgHtml->tag('h3', $heading, array('class' => 'grainDisplay'));

// parse location records into a cake tableCells compatible array
$tableArray = array();
    foreach ($grain as $index => $vendor) {
        $buttonId = $idPrefix . $vendor['id'];
        $dButtonAttr = array('id' => 'd' . $buttonId, 'bind' => 'click.addressDelete');
        $eButtonAttr = array('id' => 'e' . $buttonId);
        $location = $vendor['city'] . ', ' . $vendor['state'];
		$hash = $this->Html->div('userDisplay', null, array('id' => $this->FgHtml->secureSelect($vendor['id'])));
        $rows[] = array(
            $hash . $vendor['epms_vendor_id'],
            $vendor[$name],
            $location,
            $this->FgForm->deleteRequestButton($dButtonAttr) . ' ' . $this->FgForm->editRequestButton($eButtonAttr)
                );
    }

echo $this->FgHtml->tag('Table', null, array('class' => 'vendor'));
echo $this->Html->tableHeaders($headers);
echo $this->FgHtml->tableCells($rows)
?>
</table>
</div>