<?php
echo $this->Html->div('invoiceDisplay', null);

echo $this->Html->tag('h3', $heading, array('class' => 'grainDisplay'));

// parse location records into a cake tableCells compatible array
$tableArray = array();
$headers = array('Invoice Date', 'Invoice Number', 'Tools');
if ($grain['Invoice'] != array()) {
	echo $this->Html->div('invoiceLines', NULL);
	echo $this->Html->tag('ul', NULL);
    foreach ($grain['Invoice'] as $key => $value) {
		$b = $this->Html->link(
				'PDF', 
				array('controller' => 'invoices', 'action' => 'viewOldInvoice', $value['id'] . '.pdf'),
				array('target' => '_blank'));
		$j = ($value['job_number'] != '') ? $value['job_number'] : 'not set';
		$t = $this->Html->tag('span', $j, array('class' => 'invoiceNumber', 'bind' => 'click.editInvoiceLine', 'id' => $value['id']));
		$d = $this->Html->tag('span', $this->Time->format('m/d/Y', $value['created']), array('class' => 'invoiceDate', 'bind' => 'click.editInvoiceLine', 'id' => $value['id']));
		echo $this->FgHtml->decoratedTag(
				$d,
				'li', 
				$t . '&nbsp; &nbsp;' . $b, 
				array('class' => 'invoiceLine')
				);
//		echo $this->Form->input('Invoice.job_number');
//        $eButtonAttr = array('id' => 'einvoice' . $value['id']);
//        $rows[] = array(
//			array($this->Time->format('m/d/Y', $value['created']),array('class' => 'created')), 
//			array($value['job_number'],array('class' => 'job_number')), 
//			(($group === 'Admins' || $access === 'Manager') ? $this->FgForm->editRequestButton($eButtonAttr) : ''));
    }
	echo '</ul>';
	echo '</div>';
} else {
    $rows = array();
}

//echo $this->Html->tag('Table', null, array('class' => 'order'));
//echo $this->Html->tableHeaders($headers);
//echo $this->Html->tableCells($rows)
?>
</table>
</div>