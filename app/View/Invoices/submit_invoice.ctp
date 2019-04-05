<?php
$this->start('script');
	echo $this->FgHtml->script('invoice');
$this->end();
echo $this->FgForm->input('Invoice.id', array('type' => 'hidden', 'value' => $invoiceId));
echo $this->Html->link('Redirecting, please click to continue', array('controller' => 'invoices', 'action' => 'viewOldInvoice', $invoiceId));
?>