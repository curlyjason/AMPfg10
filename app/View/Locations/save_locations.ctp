<?php
	$message = $this->Session->flash();
	$jsonReturn = array(
		'message' => $message,
		'save' => $save
	);
	
	echo json_encode($jsonReturn);
?>