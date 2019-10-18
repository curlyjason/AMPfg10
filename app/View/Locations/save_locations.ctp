<?php
	$message = $this->Flash->render();
	$jsonReturn = array(
		'message' => $message,
		'save' => $save
	);
	
	echo json_encode($jsonReturn);
?>