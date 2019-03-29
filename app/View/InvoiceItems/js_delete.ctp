<?php
if ($result) {
	$result = array(
		'return' => $result,
		'headerTotal' => $this->Number->currency($headerTotal),
		'invoiceTotal' => $this->Number->currency($invoiceTotal),
		'headerId' => $headerId
	);
} else {
	// save failed. Send back the sad news
	$result = array('return' => $save);
}

echo json_encode($result);
?>