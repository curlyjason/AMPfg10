<?php
if ($save) {
	$result = array(
		'result' => $save,
		'invoiceItemId' => $this->request->data['InvoiceItem'][$index]['id'],
		'headerId' => $headerId,
		
		'selector' => $this->request->data['field'],
		'value' => $value,
		
		'subtotal' => $this->Number->currency($invoiceItem['InvoiceItem']['subtotal']),
		'headerTotal' => $this->Number->currency($headerTotal),
		'invoiceTotal' => $this->Number->currency($invoiceTotal)
	);
} else {
	// save failed. Send back the sad news
	$result = array('result' => $save);
}

echo json_encode($result);
?>