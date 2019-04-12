<?php
App::uses('FileExtension', 'Lib');
//$this->FgHtml->ddd($this->viewPath);
//$this->FgHtml->ddd($this->viewVars);
$tools = ($this->layout == 'ajax') ? TRUE : FALSE;

/**
 * @todo discover what the new pdf-condition detection should be 
 *		because the old params['ext'] is gone
 */
if (in_array('view', $this->params['pass']) || FileExtension::isPdf('missingHaystack')) {
	$mode = 'view';
} else {
	$mode = 'edit';
}

$rows = array();
$label = ucfirst(isset($label) ? $label : $index);
$customer = isset($invoiceCustomer['User']['username']) ? $invoiceCustomer['User']['username'] . ' ' : '';

/**
 * @todo discover what the new pdf-condition detection should be 
 *		because the old params['ext'] is gone
 */
if ($this->layout !== 'ajax') {
	if(FileExtension::isPdf('missingHaystack')){
		$cs = 6;
	} else {
		$cs = 7;
	}
	if ($label == 'General') {
		$head = array(
			array(' ', array('id' => 'section')),
			array("$label Charges", array('colspan' => $cs, 'class' => 'section'))
		);
	} else {
        $this->assign('itemTable', '');
        $this->start('itemTable');
            echo $this->Html->tag('table', NULL, array('class' => 'ordItems'));
            echo $this->Html->tableHeaders(array('Qty', 'Item', 'Price', 'SubTotal'));
            echo $this->Html->tableCells($this->Invoice->ordItems($ordItems[$index]));
            echo '</table>';
        $this->end();
		$head = array(
			array($this->InvoiceHeader->excludeOrder($invoiceHeader[$index]), array('id' => 'section')),
			array($this->InvoiceHeader->header($invoiceHeader[$index]), array('colspan' => $cs-5, 'class' => 'section')),
			array($this->fetch('itemTable'), array('colspan' => 3, 'class' => 'section'))
		);
	}
	echo $this->Html->tableCells(array($head));
}
echo $this->Html->tableHeaders($this->Invoice->makeHeaderRow($index));
//$rows[] = $this->Invoice->makeHeaderRow($index);

foreach ($data as $key => $charge) {
//	if($charge['InvoiceItem']['description'] == 'Shipping' || $charge['InvoiceItem']['description'] == 'Total Item Charges'){
	if($charge['InvoiceItem']['description'] == 'Total Item Charges'){
		$rowmode = 'view';
	} else {
		$rowmode = $mode;
	}
	$rows[] = $this->Invoice->makeChargeRow($charge, $key, $rowmode);
}

if($this->layout != 'ajax'){
	if($index != 'general'){
		$invoiceContext['order_id'] = $index;
	}
}

/**
 * @todo discover what the new pdf-condition detection should be 
 *		because the old params['ext'] is gone
 */
if (FileExtension::isPdf('missingHaystack')) {
	$rows[] = $this->Invoice->makePdfToolRow($index, $label, $invoiceTotals);
} else {
	$rows[] = $this->Invoice->makeToolRow($tools, $index, $invoiceContext, $mode, $index, $label, $invoiceTotals);
}
echo $this->Html->tableCells($rows);
?>