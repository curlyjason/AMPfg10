<?php
    echo $this->FgHtml->script('itemImport');
    echo $this->FgHtml->css('itemImport');
    if (isset($ItemRegistry))
    {
        $uploadPrompt = 'Upload a different CSV file of new items';
    } else {
        $uploadPrompt = 'Upload a CSV file of new items';
    }
?>

<div class="itemImports index">
	<div class="upload">
		<h2><?= __('Item Imports'); ?> </h2>
		<?= $this->FgForm->create('ItemImports', array('type' => 'file')); ?>
		<?= $this->Html->div('imageBlock'); ?>
		<?= $this->Html->tag('fieldset'); ?>
		<?=
            $this->FgForm->input('File.filename', array(
                'type' => 'file',
                'label' => $uploadPrompt
            ));
		?>
		<?= '</fieldset>'; //close image fieldset  ?>
		<?= '</div>'; //close the imageBlock ?>
		<?= $this->FgForm->submit(); ?>
		<?= $this->FgForm->end(); ?>
	</div>

    <?php if (isset($ItemRegistry) && $ItemRegistry->isValid()) : ?>
        <div class="map">
		<h2><?= __('Map your columns from <br/><strong><em>' . $ItemRegistry->importFileName()) .
			'</em></strong><br/>to the required columns - '; ?> </h2>
            <?= $this->FgForm->create('ItemImports', array('action' => 'preview')); ?>
            <fieldset>
                <?= $this->FgForm->label('first_row_headers', null, ['id' => 'first_row_header_label']) ?>
                <?= $this->FgForm->checkbox('first_row_headers', ['default' => 0, 'id' => 'header_row']) ?>
            </fieldset>
            <?php
                foreach ($requiredColumns as $index => $column) {
                        echo $this->FgForm->label($column);
                        echo $this->FgForm->select($column, $ItemRegistry->userColumns(), array(
                            'bind' => 'change.updateMap',
                            'node' => $index
                        ));
                    }
                echo $this->FgForm->submit();
                echo $this->FgForm->end();
            ?>
        </div>
        <div class="preview">
            <?=
            $this->FgForm->button('Previous', array(
                'class' => 'success button',
                'bind' => 'click.previousBlock',
                'type' => 'button',
                'id' => 'getSamples'
            ));
            ?>
            <?=
            $this->FgForm->button('Next', array(
                'class' => 'success button',
                'bind' => 'click.nextBlock',
                'type' => 'button',
                'id' => 'getSamples'
            ));
            ?>

            <fieldset>
                <?= $this->FgForm->input('number_of_sample', ['default' => 10, 'bind' => 'change.updatePageSettings']) ?>
                <?= $this->FgForm->input('sample_page', ['default' => 1, 'bind' => 'change.updatePageSettings']) ?>

            </fieldset>
            <div class="sample">
				<?php pr($ItemRegistry->reportErrors()); ?>
            </div>
        </div>
    <?php elseif(
				isset($ItemRegistry) && 
				($ItemRegistry->qualityIsOrange() || $ItemRegistry->qualityIsRed())
			): ?>
        <div class="preview">
            <h2><?= __("The file <strong><em>{$ItemRegistry->importFileName()}</em></strong> failed to import"); ?> </h2>
            <?php
				$errors = $ItemRegistry->ReportErrors();
				foreach ($errors['messages'] as $message) {
					echo $this->FgHtml->para('error', $message);
				}
				unset($errors['messages']);
				pr($errors);
            ?>
        </div>
    <?php endif; ?>

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
