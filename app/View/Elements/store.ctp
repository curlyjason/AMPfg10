<?php

// **************************************************
// REQUIRES
//$shopItems
//$itemLimitBudget
// other vars too
// **************************************************

$this->start('script');
echo $this->FgHtml->script('addtocart');
$this->end();

//
// TOP OF PAGE TOOLS
$hasPages = (!empty($this->request->params['paging']['Catalog']['pageCount']) && $this->request->params['paging']['Catalog']['pageCount'] > 1);


$this->start('pagination');
		echo $this->Html->tag('paginationLabel', 'page: ');
	if (!$hasPages) {
		echo '1';
		
	} else {
		echo $this->Paginator->numbers(array(
			'class' => 'paginationLink',
			'currentClass' => 'currentPaginationLink',
			'modulus' => 4,
			'first' => 1,
			'last' => 1
		));
		$list = array_keys(array_fill(1, $this->request->params['paging']['Catalog']['pageCount'], 1));
		echo $this->FgForm->input('jumpTo', array(
			'type' => 'select',
			'options' => $list,
			'empty' => 'Jump to',
			'label' => false
		));
	}
	echo $this->FgForm->input('paginationLimit', array(
		'type' => 'select',
		'options' => array(
			'1' => '1', '5' => '5', '10' => '10', '25' => '25', '50' => '50'),
		'empty' => 'Items/pg',
		'label' => false
	));
$this->end(); // pagination

$folders = false; // flag to indicate any folder LIs

// 
// STORE GRAIN OUTPUT LOOP
// Collect folders for output later
// store_grain element watches for Kits and calls for output of their components
// and it's all collected in the 'products' fetch block
$this->start('products');
foreach ($shopItems as $index => $entry) {
	if ($entry['Catalog']['folder']) {
		$folders = true;

		// This makes a list of folders in this catalog level
		// CURRENTLY UNSUPPORTED BY THE QUERY, ONLY ITEMS ARE FOUND
		$this->start('folders');
		echo $this->Html->tag('li');
		echo $this->FgHtml->secureLink($entry['Catalog']['name'], $entry['Catalog']['id'], array('controller' => 'catalogs', 'action' => 'shopping'));
		echo '</li>';
		$this->end();
		continue;
	}
	
	echo $this->element('store_grain', array(
		'entry' => $entry
	));
}
$this->end(); // products

//===================================
// Everything is processed now.
// We can output the DOM elements
//===================================
?>
<div class="pagination unsnap" bind="snap.scrollSnap" offset="-40">
<?php

echo $this->fetch('pagination');
// Collecte any folders any folders that were in this page
if ($folders) :
?>
	<div class="folders">
		<p>Other folders in this catalog</p>
		<ul class="folderItems">
			<?php echo $this->fetch('folders'); ?>
		</ul>
	</div>
<?php
endif;
?>
</div> <!-- end pagination -->

<?php echo $this->fetch('products'); ?>
