<?php
echo $this->Html->script('itemPreview');
echo $this->Html->css('itemImport');

$this->start('sidebar');
echo $this->element('sidebar_tree', array('controller' => $controller, 'tree' => $tree, 'rootNodes' => $rootNodes));
$this->end(); //end sidebar block

$message = $ItemRegistry->successfulSaveCount . ' records were saved from the file ' . $ItemRegistry->importFileName();
echo $this->Html->tag('h1', $message);

if ($ItemRegistry->hasSaveErrors()) :
?>

<table>
    <tbody>
		
		<?= $this->ItemImport->previewTableHeader($ItemRegistry); ?>
		
    <?php
    $ItemRegistry->rewind();
    while($ItemRegistry->valid()) :  
    if($ItemRegistry->item()->hasError()){
        echo $ItemRegistry->item()->itemId() . " " . $this->ItemImport->failedRow($ItemRegistry);
        echo $this->ItemImport->previewRow($ItemRegistry);
    }
	$ItemRegistry->next();
    endwhile;
    ?>
    </tbody>
</table>

<?php
endif;
