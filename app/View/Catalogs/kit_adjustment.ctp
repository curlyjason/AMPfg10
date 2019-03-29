<?php
	$message = $this->Session->flash();
	if(!$available){
		$jsonReturn = array(
			'message' => $message,
			'Available' => FALSE
		);
	} else {
		$jsonReturn = array(
			'message' => $message,
			'Available' => $available['Available']
		);
	}
	echo json_encode($jsonReturn);
//	echo $this->Session->flash('auth');
?>