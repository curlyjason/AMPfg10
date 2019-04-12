<?php
echo $this->Html->script('itemPreview');
echo $this->Html->css('itemImport');

$this->start('sidebar');
echo $this->element('sidebar_tree', array('controller' => $controller, 'tree' => $tree, 'rootNodes' => $rootNodes));
$this->end(); //end sidebar block

pr($ItemRegistry->reportErrors());
?>
<?= $this->FgForm->create('ItemImports', array('action' => 'saveItems')); ?>
<?= $this->FgForm->input('catalog.parent_id', array('type' => 'hidden', 'default' => 'unset')); ?>
<?= $this->Html->tag('span', 'Select a Destination Catalog on Left',['class' => 'company_name']); ?>
<?= $this->FgForm->end('Submit'); ?>

<table>
    <tbody>
    <?= $this->ItemImport->previewTableHeader($ItemRegistry); ?>
    <?php
    $ItemRegistry->rewind();
    while($ItemRegistry->valid()){
        echo $this->ItemImport->previewRow($ItemRegistry);
//               echo $this->Html->tableCells([$ItemRegistry->getMappedRecord($ItemRegistry->current())]);
        $ItemRegistry->next();
    }
    ?>
    </tbody>
</table>

<!--    User Fields-->
<!--    item.customer_item_code-->
<!--    item.name-->
<!--    item.description-->
<!--    item.description_2-->
<!--    item.price-->
<!--    item.initial_inventory-->
<!---->
<!--    AutoSet Fields-->
<!--    item.id-->
<!--    item.customer_owned-->
<!--    item.active-->


<!--    item.vendor_id-->
<!--    catalogs.parent_id-->
<!--    catalogs.ancestor_list-->
<!--    catalogs.sequence-->
<!--    catalogs.active-->
<!--    catalogs.customer_id-->
<!--    catalogs.customer_user_id-->
<!--    catalogs.sell_quantity-->
<!--    catalogs.sell_unit-->
<!--    catalogs.description-->
<!--    catalogs.type-->
<!--    catalogs.customer_item_code-->
<!--    -->
<!--    Additional Catalog Entries-->
<!--    catalogs.sell_quantity-->
<!--    catalogs.sell_unit-->
<!--    catalogs.description-->
